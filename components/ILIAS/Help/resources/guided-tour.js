// eslint-disable-next-line no-global-assign
il = il || {};
// il.guidedTour = il.guidedTour || {};

il.guidedTour = (function ($) {
  const compIds = new Map();
  let url; let tour; let signal; let popover; let currentTour;

  function addMapping(name, elId) {
    console.log(`addMapping: ${name} ${elId}`);
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
      options: {
        event: 'manual',
        trigger: 'manual',
      },
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
    // Beispiel: den Text der beiden Buttons ausgeben
    lastButtons.forEach((btn) => {
      if (btn.dataset.gdtrType === 'next') {
        btn.addEventListener('click', () => {
          nextStep();
        });
      }
      if (btn.dataset.gdtrType === 'close') {
        btn.addEventListener('click', () => {
          closeTour();
        });
      }
    });
  }

  function resizeIframe(iframe) {
    const doc = iframe.contentWindow.document;

    iframe.style.height = `5px`;

    doc.body.style.height = "100%";
    doc.body.style.minHeight = "100%";

    const newWidth = Math.max(
      doc.documentElement.scrollWidth,
      doc.body.scrollWidth,
    );

    let newHeight = Math.max(
      doc.documentElement.scrollHeight,
      doc.body.scrollHeight,
    );

    doc.body.style.height = "auto";
    doc.body.style.minHeight = "auto";

    newHeight = newHeight + 10;

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
          el = document.querySelector('#ilContentContainer h2');
          generateIdIfMissing(el);
          break;
        case 5: // Table
          el = document.querySelector('#ilContentContainer thead');
          generateIdIfMissing(el);
          break;
        case 6: // Toolbar
          el = document.querySelector('#mainspacekeeper .c-toolbar .c-toolbar__item');
          generateIdIfMissing(el);
          break;
        case 7: // Primary Button
          el = document.querySelector('#mainspacekeeper .btn-primary');
          generateIdIfMissing(el);
          break;
      }
    }
    return el;
  }

  function generateIdIfMissing(el) {
    if (!el.id) {
      el.id = `uid-${Math.random().toString(36).substr(2, 9)}`;
    }
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
      signal = json.popoverShowSignal;
      tour = json.tour;
      nextStep();
    });
  }

  function nextStep() {
    for (const [tourId, t] of Object.entries(tour)) {
      for (const [stepId, s] of Object.entries(t.steps)) {
        if (!s.done) {
          s.done = true;
          currentTour = tourId;
          performStep(s);
          return;
        }
      }
    }
  }

  function closeTour() {
    for (const [tourId, t] of Object.entries(tour)) {
      if (tourId === currentTour) {
        il.repository.core.fetchJson(t.finishUrl, {}).then(() => {
          hideAllPopovers();
        });
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
