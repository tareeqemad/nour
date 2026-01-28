(function () {
  "use strict";

  /* page loader */

  function hideLoader() {
    const loader = document.getElementById("loader");
    loader.classList.add("d-none")
  }

  window.addEventListener("load", hideLoader);
  /* page loader */

  /* tooltip */
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
  );

  /* popover  */
  const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
  );
  const popoverList = [...popoverTriggerList].map(
    (popoverTriggerEl) => new bootstrap.Popover(popoverTriggerEl)
  );


  /* Choices JS */
  document.addEventListener("DOMContentLoaded", function () {
    var genericExamples = document.querySelectorAll("[data-trigger]");
    for (let i = 0; i < genericExamples.length; ++i) {
      var element = genericExamples[i];
      new Choices(element, {
        allowHTML: true,
        searchEnabled: false,
        placeholderValue: "This is a placeholder set in the config",
        searchPlaceholderValue: "Search",
      });
    }
  });
  /* Choices JS */



  /* node waves */
  Waves.attach(".btn-wave", ["waves-light"]);
  Waves.init();
  /* node waves */

  /* card with close button */
  let DIV_CARD = ".card";
  let cardRemoveBtn = document.querySelectorAll(
    '[data-bs-toggle="card-remove"]'
  );
  cardRemoveBtn.forEach((ele) => {
    ele.addEventListener("click", function (e) {
      e.preventDefault();
      let $this = this;
      let card = $this.closest(DIV_CARD);
      card.remove();
      return false;
    });
  });
  /* card with close button */

  /* card with fullscreen */
  let cardFullscreenBtn = document.querySelectorAll(
    '[data-bs-toggle="card-fullscreen"]'
  );
  cardFullscreenBtn.forEach((ele) => {
    ele.addEventListener("click", function (e) {
      let $this = this;
      let card = $this.closest(DIV_CARD);
      card.classList.toggle("card-fullscreen");
      card.classList.remove("card-collapsed");
      e.preventDefault();
      return false;
    });
  });
  /* card with fullscreen */

  /* back to top */
  const scrollToTop = document.querySelector(".scrollToTop");
  const $rootElement = document.documentElement;
  const $body = document.body;
  window.onscroll = () => {
    const scrollTop = window.scrollY || window.pageYOffset;
    const clientHt = $rootElement.scrollHeight - $rootElement.clientHeight;
    if (window.scrollY > 100) {
      scrollToTop.style.display = "flex";
    } else {
      scrollToTop.style.display = "none";
    }
  };
  scrollToTop.onclick = () => {
    window.scrollTo(0, 0);
  };
  /* back to top */

  /* header dropdowns scroll */
    if (typeof SimpleBar !== 'undefined') {
        const myHeaderShortcut = document.getElementById("header-shortcut-scroll");
        if (myHeaderShortcut) new SimpleBar(myHeaderShortcut, { autoHide: true });

        const myHeadernotification = document.getElementById("header-notification-scroll");
        if (myHeadernotification) new SimpleBar(myHeadernotification, { autoHide: true });

        const myHeaderCart = document.getElementById("header-cart-items-scroll");
        if (myHeaderCart) new SimpleBar(myHeaderCart, { autoHide: true });
    } else {
        console.warn('SimpleBar غير محمّل — تم تخطي التهيئة.');
    }
  /* header dropdowns scroll */
})();

/* full screen */
var elem = document.documentElement;
function openFullscreen() {
  let open = document.querySelector(".full-screen-open");
  let close = document.querySelector(".full-screen-close");

  if (
    !document.fullscreenElement &&
    !document.webkitFullscreenElement &&
    !document.msFullscreenElement
  ) {
    if (elem.requestFullscreen) {
      elem.requestFullscreen();
    } else if (elem.webkitRequestFullscreen) {
      /* Safari */
      elem.webkitRequestFullscreen();
    } else if (elem.msRequestFullscreen) {
      /* IE11 */
      elem.msRequestFullscreen();
    }
    close.classList.add("d-block");
    close.classList.remove("d-none");
    open.classList.add("d-none");
  } else {
    if (document.exitFullscreen) {
      document.exitFullscreen();
    } else if (document.webkitExitFullscreen) {
      /* Safari */
      document.webkitExitFullscreen();
    } else if (document.msExitFullscreen) {
      /* IE11 */
      document.msExitFullscreen();
    }
    close.classList.remove("d-block");
    open.classList.remove("d-none");
    close.classList.add("d-none");
    open.classList.add("d-block");
  }
}
/* full screen */

/* toggle switches */
let customSwitch = document.querySelectorAll(".toggle");
customSwitch.forEach((e) =>
  e.addEventListener("click", () => {
    e.classList.toggle("on");
  })
);
/* toggle switches */

/* header dropdown close button - تم إزالة أكواد cart و notifications لأننا نستخدم notification-panel.js و messages-panel.js */

/* Fix nested sidebar menus - إصلاح القوائم المتداخلة في السايدبار */
document.addEventListener('DOMContentLoaded', function() {
    // إصلاح بسيط: فتح القوائم المتداخلة بناءً على العناصر النشطة فقط
    const activeMenuItems = document.querySelectorAll('.side-menu__item.active');
    
    activeMenuItems.forEach(function(activeItem) {
        // البحث عن جميع القوائم الفرعية التي تحتوي على هذا العنصر النشط
        let currentElement = activeItem.closest('ul');
        let depth = 0;
        const maxDepth = 3; // حماية من الحلقات اللانهائية
        
        while (currentElement && depth < maxDepth) {
            if (currentElement.classList.contains('slide-menu')) {
                // فتح القائمة
                currentElement.style.display = 'block';
                
                // فتح القائمة الأم
                const parentSlide = currentElement.closest('.slide.has-sub');
                if (parentSlide) {
                    parentSlide.classList.add('open');
                }
            }
            
            // الانتقال للعنصر الأب
            const nextElement = currentElement.closest('ul');
            if (!nextElement || nextElement === currentElement) break; // حماية من الحلقات
            currentElement = nextElement;
            depth++;
        }
    });
});
/* End Fix nested sidebar menus */
