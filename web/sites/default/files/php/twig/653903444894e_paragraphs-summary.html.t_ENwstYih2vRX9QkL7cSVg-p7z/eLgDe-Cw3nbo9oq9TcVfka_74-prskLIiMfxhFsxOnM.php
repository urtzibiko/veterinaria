<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/contrib/paragraphs/templates/paragraphs-summary.html.twig */
class __TwigTemplate_85a4142961e707f0a34300f7addd695e extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 16
        $context["classes"] = [0 => "paragraphs-description", 1 => ((        // line 18
($context["expanded"] ?? null)) ? ("paragraphs-expanded-description") : ("paragraphs-collapsed-description"))];
        // line 20
        ob_start(function () { return ''; });
        // line 21
        echo "  ";
        if (( !twig_test_empty(($context["content"] ?? null)) ||  !twig_test_empty(($context["behaviors"] ?? null)))) {
            // line 22
            echo "    <div";
            echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, ($context["attributes"] ?? null), "addClass", [0 => ($context["classes"] ?? null)], "method", false, false, true, 22), 22, $this->source), "html", null, true);
            echo ">
      ";
            // line 23
            if ( !twig_test_empty(($context["content"] ?? null))) {
                // line 24
                echo "        <div class=\"paragraphs-content-wrapper\">";
                // line 25
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["content"] ?? null));
                $context['loop'] = [
                  'parent' => $context['_parent'],
                  'index0' => 0,
                  'index'  => 1,
                  'first'  => true,
                ];
                if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
                    $length = count($context['_seq']);
                    $context['loop']['revindex0'] = $length - 1;
                    $context['loop']['revindex'] = $length;
                    $context['loop']['length'] = $length;
                    $context['loop']['last'] = 1 === $length;
                }
                foreach ($context['_seq'] as $context["_key"] => $context["content_item"]) {
                    // line 26
                    echo "<span class=\"summary-content\">";
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($context["content_item"], 26, $this->source), "html", null, true);
                    echo "</span>";
                    // line 27
                    if ( !twig_get_attribute($this->env, $this->source, $context["loop"], "last", [], "any", false, false, true, 27)) {
                        echo ", ";
                    }
                    ++$context['loop']['index0'];
                    ++$context['loop']['index'];
                    $context['loop']['first'] = false;
                    if (isset($context['loop']['length'])) {
                        --$context['loop']['revindex0'];
                        --$context['loop']['revindex'];
                        $context['loop']['last'] = 0 === $context['loop']['revindex0'];
                    }
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['content_item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 29
                echo "</div>
      ";
            }
            // line 31
            echo "      ";
            if ( !twig_test_empty(($context["behaviors"] ?? null))) {
                // line 32
                echo "        <div class=\"paragraphs-plugin-wrapper\">";
                // line 33
                $context['_parent'] = $context;
                $context['_seq'] = twig_ensure_traversable(($context["behaviors"] ?? null));
                foreach ($context['_seq'] as $context["_key"] => $context["behavior_item"]) {
                    // line 34
                    echo "<span class=\"summary-plugin\">";
                    // line 35
                    if ( !(null === twig_get_attribute($this->env, $this->source, $context["behavior_item"], "label", [], "any", false, false, true, 35))) {
                        // line 36
                        echo "<span class=\"summary-plugin-label\">";
                        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["behavior_item"], "label", [], "any", false, false, true, 36), 36, $this->source), "html", null, true);
                        echo "</span>";
                    }
                    // line 38
                    echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(twig_get_attribute($this->env, $this->source, $context["behavior_item"], "value", [], "any", false, false, true, 38), 38, $this->source), "html", null, true);
                    // line 39
                    echo "</span>";
                }
                $_parent = $context['_parent'];
                unset($context['_seq'], $context['_iterated'], $context['_key'], $context['behavior_item'], $context['_parent'], $context['loop']);
                $context = array_intersect_key($context, $_parent) + $_parent;
                // line 41
                echo "</div>
      ";
            }
            // line 43
            echo "    </div>
  ";
        }
        $___internal_parse_0_ = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 20
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->renderVar(twig_spaceless($___internal_parse_0_));
    }

    public function getTemplateName()
    {
        return "modules/contrib/paragraphs/templates/paragraphs-summary.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  132 => 20,  127 => 43,  123 => 41,  117 => 39,  115 => 38,  110 => 36,  108 => 35,  106 => 34,  102 => 33,  100 => 32,  97 => 31,  93 => 29,  77 => 27,  73 => 26,  56 => 25,  54 => 24,  52 => 23,  47 => 22,  44 => 21,  42 => 20,  40 => 18,  39 => 16,);
    }

    public function getSourceContext()
    {
        return new Source("", "modules/contrib/paragraphs/templates/paragraphs-summary.html.twig", "/var/www/html/web/modules/contrib/paragraphs/templates/paragraphs-summary.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array("set" => 16, "apply" => 20, "if" => 21, "for" => 25);
        static $filters = array("escape" => 22, "spaceless" => 20);
        static $functions = array();

        try {
            $this->sandbox->checkSecurity(
                ['set', 'apply', 'if', 'for'],
                ['escape', 'spaceless'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
