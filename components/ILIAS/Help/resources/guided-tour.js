// eslint-disable-next-line no-global-assign
il = il || {};
// il.guidedTour = il.guidedTour || {};

il.guidedTour = (function ($) {
  const compIds = new Map();
  let url; let popover; let signal;

  function addMapping(name, elId) {
    /* console.log(`addMapping: ${name} ${elId}`); */
    compIds.set(name, elId);
  }

  function trigger(el, s) {
    $(el).trigger(s, {
      id: s,
      event: new Event('click'),
      triggerer: $(el),
      options: JSON.parse('[]'),
    });
  }

  function hideAllPopovers() {
    WebuiPopovers.hideAll();
  }

  function showPopover(elName) {
    const elId = compIds.get(elName);
    hideAllPopovers();
    if (elId) {
      let el = document.getElementById(elId);
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
      if (el) {
        trigger(el, signal);
      }
    }
  }

  /* function fetchHTML(cmd) {
    return il.repository.core.fetchHtml(url, { cmd });
  } */

  function fetchJson(cmd) {
    return il.repository.core.fetchJson(url, { cmd });
  }

  function loadPopup() {
    fetchJson('getPopover').then((json) => {
      const main = document.querySelector('main');
      il.repository.core.appendHTML(main, json.html);
      signal = json.showSignal;
      showPopover('notification_center');
      /* setTimeout(() => {
        showPopover('notification_center');
      }, 1000); */
    });
  }

  function init(u) {
    url = u;
    setTimeout(() => {
      loadPopup();
    }, 200);
  }

  return {
    addMapping,
    init,
  };
  // eslint-disable-next-line no-undef
}($));
