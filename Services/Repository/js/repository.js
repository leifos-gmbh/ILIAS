"use strict";
/* global il, $ */

il = il || {};
il.repository = il.repository || {};

il.repository.ui = (function(il, $) {
  // All functions now have direct access to each other

  const sendAsync = function (form, replace = null) {
    const data = new URLSearchParams();
    for (const pair of new FormData(form)) {
      data.append(pair[0], pair[1]);
    }
    fetch(form.action, {
      method: 'POST',
      mode: 'same-origin',
      cache: 'no-cache',
      credentials: 'same-origin',
      redirect: 'follow',
      referrerPolicy: 'same-origin',
      body: data
    }).then(response => {
      response.text().then(text => {
          if (replace) {
            const marker = "component";
            var $new_content = $("<div>" + text + "</div>");
            var $marked_new_content = $new_content.find("[data-replace-marker='" + marker + "']").first();

            if ($marked_new_content.length == 0) {

              // if marker does not come with the new content, we put the new content into the existing element
              $(replace).html(text);

            } else {

              // if marker is in new content, we replace the complete old node with the marker
              // with the new marked node
              $(replace).find("[data-replace-marker='" + marker + "']").first()
              .replaceWith($marked_new_content);

              // append included script (which will not be part of the marked node
              $(replace).find("[data-replace-marker='" + marker + "']").first()
              .after($new_content.find("[data-replace-marker='script']"));
            }
          }
        }
      );
    });
  };

  const initForms = function () {
    document.querySelectorAll("form[data-rep-modal-form='async']:not([data-rep-form-initialised='1'])").forEach(f => {
      f.addEventListener("submit", (event) => {
        event.preventDefault();
        const modal = f.closest(".modal");
        sendAsync(f, modal);
      });
      f.querySelectorAll(".il-standard-form-cmd").forEach(b => {
        b.style.display='none';
      });
      f.dataset.repFormInitialised = '1';
    });
    document.querySelectorAll("form[data-rep-modal-form='sync']:not([data-rep-form-initialised='1'])").forEach(f => {
      f.querySelectorAll(".il-standard-form-cmd").forEach(b => {
        b.style.display='none';
      });
      f.dataset.repFormInitialised = '1';
    });
  };

  const init = function() {
    initForms();
  };

  const submitModalForm = function(event, sentAsync) {
    console.log("one");
    const f = event.target.closest(".modal").querySelector("form");
    console.log(f);
    const modal = f.closest(".modal");
    if (sentAsync) {
      sendAsync(f, modal);
    } else {
      f.submit();
    }
  };

  return {
    init: init,
    submitModalForm: submitModalForm
  };
}(il, $));