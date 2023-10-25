(function ($) {
    'use strict';

    const videoCovers = gsap.utils.toArray('.js-video-cover');

    const VIDEO_SELECTOR = '.js-video-worker';
    const videosWrapper = document.querySelectorAll(VIDEO_SELECTOR);

    let videos = [];


    videoCovers.forEach((videoCover, key) => {

      videoCover.setAttribute('data-id', key);
      const videoCoverTitle = videoCover.querySelector(".js-video-cover-title");
      const videoCoverPlay = videoCover.querySelector(".js-video-cover-play");

      gsap.fromTo(
        [videoCoverTitle, videoCoverPlay],
        { y: 100 },
        { y: 0, opacity: 1, duration: 3, ease: "expo.out", scrollTrigger: {
            trigger: videoCover,
            start: "60% bottom"
          }
      });

      videoCover.addEventListener("click", () => {
        let id = videoCover.dataset.id;
        pauseVideos(id);
        videos[id].play();
        gsap.timeline().to(videoCover, {
          opacity: 0,
          pointerEvents: "none"
        });

      });
    });

    videosWrapper.forEach((item, index) => {
      if (!item.dataset.src) {
        console.dir(item);
      }

      videos.push(new VideoWorker(item.dataset.src));
      if (videos[index].isValid()) {
        videos[index].getVideo((iframe) => {
          item.appendChild(iframe);
        });
      }
      videos[index].on('play', () => {
        pauseVideos(index);
      });
    });


    function pauseVideos(id) {
      videos.forEach((video, key) => {
        if (video['ID'] !== id) {
          video.pause();
        }
      });
    }


})(jQuery);
