(function () {
  var revealItems = document.querySelectorAll('.reveal');

  if (!('IntersectionObserver' in window) || !revealItems.length) {
    return;
  }

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    },
    {
      rootMargin: '0px 0px -10% 0px',
      threshold: 0.2
    }
  );

  revealItems.forEach(function (item) {
    observer.observe(item);
  });
})();
