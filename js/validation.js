/**
 * Project Tracking System - Form Validation
 * A lightweight, reusable validation engine.
 *
 * Usage:
 *   <form data-validate>
 *     <input data-rules='{"required":true,"minLength":2}'>
 *     <input data-rules='{"required":true,"pattern":"email"}'>
 *   </form>
 */

(function () {
  "use strict";

  // ---- Rule definitions ----
  var RULES = {
    required: {
      validate: function (value) {
        if (typeof value === "string") return value.trim().length > 0;
        return value !== null && value !== undefined;
      },
      message: "This field is required.",
    },
    minLength: {
      validate: function (value, min) {
        return value.trim().length >= min;
      },
      message: function (min) {
        return "Must be at least " + min + " characters.";
      },
    },
    maxLength: {
      validate: function (value, max) {
        return value.trim().length <= max;
      },
      message: function (max) {
        return "Must be no more than " + max + " characters.";
      },
    },
    numeric: {
      validate: function (value) {
        return /^-?\d+(\.\d+)?$/.test(value.trim());
      },
      message: "Must be a number.",
    },
    integer: {
      validate: function (value) {
        return /^-?\d+$/.test(value.trim());
      },
      message: "Must be a whole number.",
    },
    pattern: {
      validate: function (value, pattern) {
        if (pattern === "email") {
          return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim());
        }
        if (pattern === "date") {
          return /^\d{1,2}\/\d{1,2}\/\d{4}$/.test(value.trim());
        }
        try {
          return new RegExp(pattern).test(value.trim());
        } catch (e) {
          return true;
        }
      },
      message: function (pattern) {
        if (pattern === "email") return "Please enter a valid email address.";
        if (pattern === "date") return "Please use MM/DD/YYYY format.";
        return "Invalid format.";
      },
    },
  };

  // ---- Error display ----
  function getErrorContainer(input) {
    var el = input.parentNode.querySelector(".field-error");
    if (!el) {
      el = document.createElement("span");
      el.className = "field-error";
      input.parentNode.appendChild(el);
    }
    return el;
  }

  function showError(input, message) {
    input.classList.add("is-invalid");
    input.classList.remove("is-valid");
    var container = getErrorContainer(input);
    container.textContent = message;
    container.style.display = "block";
  }

  function clearError(input) {
    input.classList.remove("is-invalid");
    input.classList.add("is-valid");
    var container = input.parentNode.querySelector(".field-error");
    if (container) {
      container.textContent = "";
      container.style.display = "none";
    }
  }

  // ---- Field validation ----
  function validateField(input) {
    var rulesAttr = input.getAttribute("data-rules");
    if (!rulesAttr) return true;

    var rules;
    try {
      rules = JSON.parse(rulesAttr);
    } catch (e) {
      return true;
    }

    var value = input.value || "";
    var isCheckbox = input.type === "checkbox";
    var isSelect = input.tagName === "SELECT";

    for (var key in rules) {
      if (!rules.hasOwnProperty(key)) continue;
      var ruleDef = RULES[key];
      if (!ruleDef) continue;

      var param = rules[key];
      var valid;

      if (isCheckbox && key === "required") {
        valid = input.checked;
      } else {
        valid = ruleDef.validate(value, param);
      }

      if (!valid) {
        var msg =
          typeof ruleDef.message === "function"
            ? ruleDef.message(param)
            : ruleDef.message;
        showError(input, msg);
        return false;
      }
    }

    clearError(input);
    return true;
  }

  // ---- Form validation ----
  function validateForm(form) {
    var inputs = form.querySelectorAll(
      "[data-rules]:not([disabled])"
    );
    var allValid = true;

    for (var i = 0; i < inputs.length; i++) {
      var valid = validateField(inputs[i]);
      if (!valid) allValid = false;
    }

    if (!allValid) {
      // Focus first invalid field
      var firstInvalid = form.querySelector(".is-invalid");
      if (firstInvalid) firstInvalid.focus();
    }

    return allValid;
  }

  // ---- Conflicting checkbox check ----
  function checkConflictingCheckboxes(form) {
    var deleteCb = form.querySelector(
      'input[name="delete"][type="checkbox"]'
    );
    var adminCb = form.querySelector(
      'input[name="admin"][type="checkbox"]'
    );

    if (deleteCb && adminCb) {
      function sync() {
        if (deleteCb.checked && adminCb.checked) {
          adminCb.checked = false;
        }
      }
      deleteCb.addEventListener("change", sync);
      adminCb.addEventListener("change", sync);
    }
  }

  // ---- Submit button loading state ----
  function setLoading(form, loading) {
    var btn = form.querySelector('[type="submit"]');
    if (!btn) return;
    if (loading) {
      btn._originalText = btn.value;
      btn.value = "Processing...";
      btn.disabled = true;
    } else {
      btn.value = btn._originalText || btn.value;
      btn.disabled = false;
    }
  }

  // ---- Init ----
  function init() {
    var forms = document.querySelectorAll("form[data-validate]");

    forms.forEach(function (form) {
      // Validate on blur
      var inputs = form.querySelectorAll("[data-rules]");
      for (var i = 0; i < inputs.length; i++) {
        (function (input) {
          input.addEventListener("blur", function () {
            validateField(input);
          });
          input.addEventListener("input", function () {
            // Only re-validate if already touched (has is-invalid or is-valid)
            if (
              input.classList.contains("is-invalid") ||
              input.classList.contains("is-valid")
            ) {
              validateField(input);
            }
          });
        })(inputs[i]);
      }

      // Handle conflicting checkboxes (delete + admin)
      checkConflictingCheckboxes(form);

      // Validate on submit
      form.addEventListener("submit", function (e) {
        var valid = validateForm(form);
        if (!valid) {
          e.preventDefault();
          return;
        }
        setLoading(form, true);
      });
    });
  }

  // Run on DOMContentLoaded
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
