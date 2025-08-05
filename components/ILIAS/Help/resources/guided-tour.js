// eslint-disable-next-line no-global-assign
il = il || {};
// il.guidedTour = il.guidedTour || {};

il.guidedTour = (function ($) {
  const compIds = new Map();
  let url; let tour; let signal; let popover;

  function addMapping(name, elId) {
    /* console.log(`addMapping: ${name} ${elId}`); */
    compIds.set(name, elId);
  }

  function getScrollableContainer() {
    if (window.innerWidth < 768) {
      return document.querySelector('body');
    }
    return document.querySelector('.il-layout-page-content');
  }


  function trigger(el, s) {
    $(document).trigger(s, {
      id: s,
      event: null,
      triggerer: $(el),
      options: JSON.parse('[]'),
    });
  }

  function hideAllPopovers() {
    WebuiPopovers.hideAll();
  }

  function showPopover(type, elName, stepUrl) {
    const el = getTriggerElement(type, elName);
    const contentEl = popover;
    contentEl.innerHTML = `<iframe style="border:0; height: 5px; display:inline-block; width:20em; margin: 15px;" src='${stepUrl}'></iframe>`;
    hideAllPopovers();
    if (el) {
      trigger(el, signal);
      const iframe = contentEl.querySelector('iframe');
      let first = true;
      console.log("in show");
      iframe.addEventListener('load', () => {
        console.log("load");
        resizeIframe(iframe);
        if (first) {
          const scrollContainer = getScrollableContainer();
          scrollContainer.dispatchEvent(new Event('scroll'));
          first = false;
        }
        addButtonListeners(iframe);
      });
      window.addEventListener('resize', () => {
        console.log("resize");
        resizeIframe(iframe);
      });
      // resizeIframe(iframe);
    }
  }

  function addButtonListeners(iframe) {
    const doc = iframe.contentDocument || iframe.contentWindow.document;
    const buttons = [...doc.querySelectorAll('button')];   // NodeList → Array
    const lastButtons = buttons.slice(-2);
console.log("1");
    // Beispiel: den Text der beiden Buttons ausgeben
    lastButtons.forEach((btn) => {
      console.log("2");
      console.log(btn.dataset);
      if (btn.dataset.gdtrType === 'next') {
        console.log("3");
        btn.addEventListener('click', () => {
          console.log("4");
          nextStep();
        });
      }
    });
  }

  function resizeIframe(iframe) {
    const doc = iframe.contentWindow.document;

    iframe.style.height = `5px`;

    const newWidth = Math.max(
      doc.documentElement.scrollWidth,
      doc.body.scrollWidth,
    );

    const newHeight = Math.max(
      doc.documentElement.scrollHeight,
      doc.body.scrollHeight,
    );

    // iframe.style.width = `${newWidth}px`;
    iframe.style.height = `${newHeight}px`;
  }

  function getTriggerElement(type, elName) {
    let el;
    // main, meta and tabs
    if (elName != '') {
      const elId = compIds.get(elName);
      if (elId) {
        el = document.getElementById(elId);
        // check if we have a mainbar slate instead of the button
        if (el.classList.contains('il-maincontrols-slate')) {
          const metabar = el.closest('.il-metabar-slates');
          if (metabar) {
            // metabar
            el = metabar.parentNode.querySelector('button');
          } else {
            // mainbar
            el = document.querySelector(`button[aria-controls="${elId}"]`);
          }
        }
      }
    } else {
      switch (type) {
        case 4: // Form
          el = document.querySelector('#mainspacekeeper .c-form__header');
          break;
        case 5: // Table
          el = document.querySelector('#mainspacekeeper thead');
          break;
        case 6: // Toolbar
          el = document.querySelector('#mainspacekeeper .c-toolbar');
          break;
        case 7: // Primary Button
          el = document.querySelector('#mainspacekeeper .btn-primary');
          break;
      }
    }
    return el;
  }

  /* function fetchHTML(cmd) {
    return il.repository.core.fetchHtml(url, { cmd });
  } */

  function fetchJson(cmd) {
    return il.repository.core.fetchJson(url, { cmd });
  }

  function loadData() {
    fetchJson('getData').then((json) => {
      const main = document.querySelector('main');
      il.repository.core.appendHTML(main, json.popoverHtml);
      popover = document.querySelector('main .il-standard-popover-content');
      console.log("###");
      console.log(popover);
      signal = json.popoverShowSignal;
      // showPopover('notification_center');
      tour = json.tour;
      nextStep();
      /* setTimeout(() => {
        showPopover('notification_center');
      }, 1000); */
    });
  }

  function nextStep() {
    for (const [tourId, t] of Object.entries(tour)) {
      for (const [stepId, s] of Object.entries(t.steps)) {
        if (!s.done) {
          s.done = true;
          performStep(s);
          return;
        }
      }
    }
  }

  function performStep(s) {
    showPopover(s.type, s.elementId, s.url);
  }

  function init(u) {
    url = u;
    setTimeout(() => {
      loadData();
    }, 200);
  }

  return {
    addMapping,
    init,
  };
  // eslint-disable-next-line no-undef
}($));
