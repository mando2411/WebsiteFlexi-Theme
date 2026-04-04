(function () {
  var revealItems = document.querySelectorAll('.reveal, .reveal-stagger');
  var counterItems = document.querySelectorAll('[data-counter]');
  var parallaxItems = document.querySelectorAll('[data-parallax]');
  var menuToggle = document.querySelector('.menu-toggle');
  var headerTools = document.querySelector('#header-tools');

  if (menuToggle && headerTools) {
    menuToggle.addEventListener('click', function () {
      var isOpen = headerTools.classList.toggle('is-open');
      menuToggle.classList.toggle('is-open', isOpen);
      menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      document.body.classList.toggle('menu-open', isOpen);
    });

    document.addEventListener('click', function (event) {
      if (!headerTools.classList.contains('is-open')) {
        return;
      }

      if (headerTools.contains(event.target) || menuToggle.contains(event.target)) {
        return;
      }

      headerTools.classList.remove('is-open');
      menuToggle.classList.remove('is-open');
      menuToggle.setAttribute('aria-expanded', 'false');
      document.body.classList.remove('menu-open');
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 900) {
        headerTools.classList.remove('is-open');
        menuToggle.classList.remove('is-open');
        menuToggle.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('menu-open');
      }
    });
  }

  if (!('IntersectionObserver' in window) || !revealItems.length) {
    revealItems.forEach(function (item) {
      item.classList.add('is-visible');
    });
  } else {
    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            var delay = parseInt(entry.target.getAttribute('data-reveal-delay') || '0', 10);

            if (delay > 0) {
              entry.target.style.transitionDelay = (delay * 85) + 'ms';
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
          }
        });
      },
      {
        rootMargin: '0px 0px -12% 0px',
        threshold: 0.15
      }
    );

    revealItems.forEach(function (item) {
      observer.observe(item);
    });
  }

  if (counterItems.length) {
    counterItems.forEach(function (counter) {
      counter.textContent = '0';
    });

    var counterObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) {
            return;
          }

          var node = entry.target;
          var maxValue = parseInt(node.getAttribute('data-counter') || '0', 10);
          var duration = 1200;
          var start = null;

          function tick(timeStamp) {
            if (!start) {
              start = timeStamp;
            }

            var progress = Math.min((timeStamp - start) / duration, 1);
            node.textContent = String(Math.floor(progress * maxValue));

            if (progress < 1) {
              window.requestAnimationFrame(tick);
            } else {
              node.textContent = String(maxValue);
            }
          }

          window.requestAnimationFrame(tick);
          counterObserver.unobserve(node);
        });
      },
      {
        threshold: 0.3
      }
    );

    counterItems.forEach(function (counter) {
      counterObserver.observe(counter);
    });
  }

  if (parallaxItems.length) {
    window.addEventListener('scroll', function () {
      var offset = window.scrollY || window.pageYOffset;

      parallaxItems.forEach(function (item) {
        var move = Math.min(offset * 0.08, 26);
        item.style.transform = 'translateY(' + move + 'px)';
      });
    }, { passive: true });
  }
})();
