<?php

namespace Drupal\background_image\Controller;

use Drupal\background_image\BackgroundImageInterface;
use Drupal\background_image\BackgroundImageManagerInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\system\FileDownloadController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

/**
 * Defines a controller to serve image styles.
 */
class BackgroundImageCssController extends FileDownloadController {

  /**
   * @var \Drupal\background_image\BackgroundImageManagerInterface
   */
  protected $backgroundImageManager;

  /**
   * @var \Drupal\breakpoint\BreakpointManagerInterface
   */
  protected $breakpointManager;

  /**
   * A cache array of CSS template filenames, keyed by theme name.
   *
   * @var array
   */
  protected $cssTemplates;

  /**
   * The File System service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Flag indicating whether to gzip files.
   *
   * @var bool
   */
  protected $gzip = FALSE;

  /**
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The cache service.
   *
   * @var \Drupal\Core\Cache\Cache
   */
  protected $cache;

  /**
   * The module extension list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * @var \Twig_Environment
   */
  protected $twig;

  /**
   * Constructs a BackgroundImageCssController object.
   *
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $streamWrapperManager
   *   The Stream Wrapper Manager service.
   * @param \Drupal\background_image\BackgroundImageManagerInterface $background_image_manager
   *   The Background Image Manager service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The File System service.
   * @param \Drupal\Core\Image\ImageFactory $image_factory
   *   The Image Factory service.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The Lock service.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The Theme Manager service.
   * @param \Drupal\Core\Cache\Cache $cache
   *   The cache service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   *   The module extension list service.
   * @param \Twig_Environment $twig
   *   The Twig service.
   */
  public function __construct(StreamWrapperManagerInterface $streamWrapperManager, BackgroundImageManagerInterface $background_image_manager, FileSystemInterface $fileSystem, ImageFactory $image_factory, LockBackendInterface $lock, ThemeManagerInterface $theme_manager, Cache $cache, ModuleExtensionList $moduleExtensionList, TwigEnvironment $twig) {
    parent::__construct($streamWrapperManager);
    $this->backgroundImageManager = $background_image_manager;
    $this->fileSystem = $fileSystem;
    $this->gzip = extension_loaded('zlib') && \Drupal::config('system.performance')->get('css.gzip');
    $this->imageFactory = $image_factory;
    $this->lock = $lock;
    $this->logger = $this->getLogger('background_image');
    $this->themeManager = $theme_manager;
    $this->cache = $cache;
    $this->moduleExtensionList = $moduleExtensionList;
    $this->twig = $twig;
    if ($this->moduleHandler()->moduleExists('responsive_image')) {
      $this->breakpointManager = \Drupal::service('breakpoint.manager');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('stream_wrapper_manager'),
      $container->get('background_image.manager'),
      $container->get('file_system'),
      $container->get('image.factory'),
      $container->get('lock'),
      $container->get('theme.manager'),
      $container->get('cache_factory'),
      $container->get('extension.list.module'),
      $container->get('twig')
    );
  }

  /**
   * Generates the necessary CSS for a background image.
   *
   * @param \Drupal\background_image\BackgroundImageInterface $background_image
   *   A background image entity.
   * @param string $uri
   *   The URI path on where to store the generated CSS.
   *
   * @return bool
   *   TRUE or FALSE
   */
  public function buildCss(BackgroundImageInterface $background_image, $uri) {
    // Immediately return if there is is no image file.
    if (!$background_image->getImageFile()) {
      $this->logger->error('Background image does not have a valid image file: background_image:@id', ['@id' => $background_image->id()]);
      return FALSE;
    }

    // Build the destination folder tree if it doesn't already exist.
    $directory = $this->fileSystem->dirname($uri);
    if (!$this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      $this->logger->error('Failed to create background image directory: %directory', ['%directory' => $directory]);
      return FALSE;
    }

    $variables = [
      'base_class' => $this->backgroundImageManager->getBaseClass(),
      'background_image_class' => $background_image->getCssClass(),
      'settings' => $background_image->getSettings()->get(),
      'preload_url' => $background_image->getImageUrl($this->backgroundImageManager->getPreloadImageStyle()),
      'fallback_url' => $background_image->getImageUrl($this->backgroundImageManager->getFallbackImageStyle()),
      'media_queries' => $this->buildMediaQueries($background_image),
    ];

    $cssTemplate = $this->getCssTemplate();
    $this->moduleHandler()->alter('background_image_css_template', $variables, $cssTemplate, $background_image);
    $this->themeManager->alter('background_image_css_template', $variables, $cssTemplate, $background_image);

    // Render the template.
    try {
      $data = $this->twig->load($cssTemplate)->render($variables);
      // Minify the CSS if necessary.
      if (preg_match('/\.min\.css$/', $uri) && ($css_minifier = $this->backgroundImageManager->getCssMinifier())) {
        $data = $css_minifier->optimize($data, [], []);
        $css_minifier->addLicense($data, preg_replace('/\.min\.css$/', '.css', file_create_url($uri)));
      }
      if (!$this->dump($data, $uri)) {
        return FALSE;
      }
    }
    catch (\Exception $e) {
      $previous_exception = $e->getPrevious();
      $this->logger->error($previous_exception ? $previous_exception->getMessage() : $e->getMessage());
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Build a list of media queries.
   *
   * @param \Drupal\background_image\BackgroundImageInterface $background_image
   *   The background image entity.
   *
   * @return array
   *   An sorted indexed array of associative arrays.
   */
  protected function buildMediaQueries(BackgroundImageInterface $background_image) {
    $responsive_image_style = $this->backgroundImageManager->getResponsiveImageStyle();

    // Immediately return if there is no responsive image style.
    if (!$this->breakpointManager || !$responsive_image_style) {
      return [];
    }

    $mediaQueries = [];

    // Get the necessary variables.
    $breakpoints = $this->breakpointManager->getBreakpointsByGroup($responsive_image_style->getBreakpointGroup());
    $keyed_image_style_mappings = $responsive_image_style->getKeyedImageStyleMappings();
    $retinaRules = $this->backgroundImageManager->getRetinaRules();

    // Retrieve the responsive image sources.
    $i = 0;
    foreach ($breakpoints as $breakpoint_id => $breakpoint) {
      if (isset($keyed_image_style_mappings[$breakpoint_id])) {
        $mediaQuery = trim($breakpoint->getMediaQuery());
        foreach ($keyed_image_style_mappings[$breakpoint_id] as $multiplier => $image_style_mapping) {
          if ($image_style_mapping['image_mapping_type'] !== 'image_style') {
            continue;
          }

          // Use multiplier as a key so it can be sorted in the array later.
          $key = intval(mb_substr($multiplier, 0, -1) * 100) + $i++;
          $image_style = $image_style_mapping['image_mapping'];

          // Merge the multiplier with retina rules.
          if ($multiplier === "2x") {
            $rules = [];
            foreach ($retinaRules as $retinaRule) {
              $rules[] = trim($retinaRule) . ' and ' . trim(preg_replace('/^\s*(only )?(all|print|screen)\s?(and)?/', '', $mediaQuery));
            }
            $mediaQueries[$key] = [
              'image_style' => $image_style,
              'multiplier' => $multiplier,
              'query' => implode(',', $rules),
              'url' => $background_image->getImageUrl($image_style),
            ];
          }
          else {
            $mediaQueries[$key] = [
              'image_style' => $image_style,
              'multiplier' => $multiplier,
              'query' => $mediaQuery,
              'url' => $background_image->getImageUrl($image_style),
            ];
          }
        }
      }
    }

    // Sort the the media queries so the multipliers are aft
    ksort($mediaQueries);

    return $mediaQueries;
  }

  /**
   * {@inheritdoc}
   */
  protected function dump($data, $uri) {
    // Save the file.
    $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);

    try {
      $this->fileSystem->saveData($data, $uri, FileSystemInterface::EXISTS_REPLACE);
      // Create gzipped file.
      if ($this->gzip) {
        $this->fileSystem->saveData(gzencode($data, 9, FORCE_GZIP), $uri . '.gz', FileSystemInterface::EXISTS_REPLACE);
      }
    }
    catch (FileWriteException $e) {
      $this->logger->error($this->t('Unable to create save the CSS file: @uri', [
        '@uri' => $uri,
      ]));
      return FALSE;
    }
    catch (FileException $e) {
      return FALSE;
    }
    return $uri;
  }

  /**
   * Duplication of _responsive_image_image_style_url().
   *
   * This is needed so this module does not have to provide a hard dependency
   * on the responsive_image module.
   *
   * @param string $style_name
   *   The style name to use.
   * @param string $path
   *   The path of the image file.
   *
   * @return string
   *   The image style URL.
   *
   * @deprecated in 8.x-1.4, will be removed in 8.x-2.0. Use
   *   \Drupal\background_image\BackgroundImageInterface::getImageUrl() instead.
   */
  public static function imageStyleUrl($style_name, $path) {
    if ($style_name == '_empty image_') {
      // The smallest data URI for a 1px square transparent GIF image.
      // http://probablyprogramming.com/2009/03/15/the-tiniest-gif-ever
      return 'data:image/gif;base64,R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    }
    $fileUrlGeneratorService = \Drupal::service('file_url_generator');
    $entity = ImageStyle::load($style_name);
    if ($entity instanceof ImageStyle) {
      $url = $entity->buildUrl($path);
    }
    else {
      $url = $fileUrlGeneratorService->generateAbsoluteString($path);
    }
    return $fileUrlGeneratorService->transformRelative($url);
  }

  /**
   * Generates a background CSS file.
   *
   * After generating an image, transfer it to the requesting agent.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   * @param \Drupal\background_image\BackgroundImageInterface $background_image
   *   The background image entity.
   * @param string $scheme
   *   The file scheme, defaults to 'private'.
   * @param string $file
   *   The file name to generate.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\Response
   *   The transferred file as response or some error response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
   *   Thrown when the user does not have access to the file.
   * @throws \Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException
   *   Thrown when the file is still being generated.
   */
  public function deliver(Request $request, BackgroundImageInterface $background_image, $scheme, $file = NULL) {
    // Ensure this is a valid background image, scheme and user has permission.
    $valid = !empty($background_image) && $this->streamWrapperManager->isValidScheme($scheme) && $background_image->access('view');
    if (!$valid) {
      throw new AccessDeniedHttpException();
    }

    $uri = "$scheme://background_image/css/{$background_image->id()}/$scheme/$file";

    // If using the private scheme, let other modules provide headers and
    // control access to the file.
    $headers = [];
    if ($scheme == 'private') {
      $headers = $this->moduleHandler()->invokeAll('file_download', [$uri]);
      if (in_array(-1, $headers) || empty($headers)) {
        throw new AccessDeniedHttpException();
      }
    }

    // Don't start generating the image if the derivative already exists or if
    // generation is in progress in another thread.
    if (!file_exists($uri)) {
      $lock_name = 'background_image_css_deliver:' . $background_image->id() . ':' . $background_image->getImageHash();
      $lock_acquired = $this->lock->acquire($lock_name);
      if (!$lock_acquired) {
        // Tell client to retry again in 3 seconds. Currently no browsers are
        // known to support Retry-After.
        throw new ServiceUnavailableHttpException(3, $this->t('Background Image CSS generation in progress. Try again shortly.'));
      }
    }

    // Try to generate the image, unless another thread just did it while we
    // were acquiring the lock.
    $success = file_exists($uri) || $this->buildCss($background_image, $uri);

    if (!empty($lock_acquired)) {
      $this->lock->release($lock_name);
    }

    if ($success) {
      $headers += [
        'Content-Type' => 'text/css',
        'Content-Length' => filesize($uri),
      ];
      // \Drupal\Core\EventSubscriber\FinishResponseSubscriber::onRespond()
      // sets response as not cacheable if the Cache-Control header is not
      // already modified. We pass in FALSE for non-private schemes for the
      // $public parameter to make sure we don't change the headers.
      return new BinaryFileResponse($uri, 200, $headers, $scheme !== 'private');
    }
    else {
      $this->logger->notice('Unable to generate the background image CSS located at %path.', ['%path' => $uri]);
      return new Response($this->t('Error generating image.'), 500);
    }
  }

  /**
   * Retrieves the Twig template filename used to generate the necessary CSS.
   *
   * @return string
   */
  protected function getCssTemplate() {
    if (!isset($this->cssTemplates)) {
      $cache = $this->cache->get('background_image_css_templates');
      $this->cssTemplates = $cache && is_array($cache->data) ? $cache->data : [];
    }

    $activeTheme =  $this->themeManager->getActiveTheme();
    $activeThemeName = $activeTheme->getName();

    if (!isset($this->cssTemplates[$activeThemeName])) {
      // Search for a theme based template file.
      $templatePaths = [$activeTheme->getPath() . '/templates'];
      foreach ($activeTheme->getBaseThemeExtensions() as $name => $extension) {
        $templatePaths[$name] = $extension->getPath() . '/templates';
      }

      $mask = '/' . preg_quote('background_image.css.twig', '/') . '$/';
      foreach (array_unique($templatePaths) as $path) {
        try {
          if (is_dir($path) && ($file = current($this->fileSystem->scanDirectory($path, $mask)))) {
            $cssTemplate = str_replace(\Drupal::root() . '/', '', $file->uri);
            break;
          }
        }
        catch (FileException $e) {
          // Intentionally ignored, do nothing.
        }
      }

      // Default to this module's template if none was found.
      if (!isset($cssTemplate)) {
        $cssTemplate = $this->moduleExtensionList->getPath('background_image') . '/templates/background_image.css.twig';
      }

      $this->cssTemplates[$activeThemeName] = $cssTemplate;
      $this->cache->set('background_image_css_templates', $this->cssTemplates);
    }

    return $this->cssTemplates[$activeThemeName];
  }

}
