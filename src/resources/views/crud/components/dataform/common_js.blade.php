@verbatim
if (typeof window.initializeFieldsWithJavascript === 'undefined') {
    window.initializeFieldsWithJavascript = function(container) {
        var selector;
        if (container instanceof jQuery) {
            selector = container;
        } else {
            selector = $(container);
        }
        
        var fieldsToInit = selector.find("[data-init-function]").not("[data-initialized=true]");
        
        fieldsToInit.each(function () {
            var element = $(this);
            var functionName = element.data('init-function');

            if (typeof window[functionName] === "function") {
                try {
                    window[functionName](element);
                    element.attr('data-initialized', 'true');
                } catch (error) {
                    element.attr('data-initialized', 'true');
                    console.error('[FieldInit] Error initializing field with function ' + functionName + ':', error);
                }
            }
        });
    };
}

if (!window._select2FocusFixInstalled) {
    document.addEventListener('focusin', function(e) {
        if (e.target.classList.contains('select2-search__field') ||
            e.target.closest('.select2-container') ||
            e.target.closest('.select2-dropdown')) {
            e.stopImmediatePropagation();
        }
    }, true);
        
    window._select2FocusFixInstalled = true;
}

/**
 * Auto-discover first focusable input
 * @param {jQuery} form
 * @return {jQuery}
 */
function getFirstFocusableField(form) {
    return form.find('input, select, textarea, button')
        .not('.close')
        .not('[disabled]')
        .filter(':visible:first');
}

/**
 *
 * @param {jQuery} firstField
 */
function triggerFocusOnFirstInputField(firstField) {
    if (firstField.hasClass('select2-hidden-accessible')) {
        return handleFocusOnSelect2Field(firstField);
    }

    firstField.trigger('focus');
}

/**
 * 1- Make sure no other select2 input is open in other field to focus on the right one
 * 2- Check until select2 is initialized
 * 3- Open select2
 *
 * @param {jQuery} firstField
 */
function handleFocusOnSelect2Field(firstField){
    firstField.select2('focus');
}

/*
* Hacky fix for a bug in select2 with jQuery 3.6.0's new nested-focus "protection"
* see: https://github.com/select2/select2/issues/5993
* see: https://github.com/jquery/jquery/issues/4382
*
*/
$(document).on('select2:open', () => {
    setTimeout(() => document.querySelector('.select2-container--open .select2-search__field').focus(), 100);
});

// When Select2 opens inside a repeatable row that is itself inside a modal,
// add a specific class to the open container so CSS/positioning logic can target it.
// Also remove the class on close.
$(document).on('select2:open', function(e) {
    // The event target will be the original select element
    try {
        var $select = $(e.target);
        var $repeatable = $select.closest('.repeatable-element');
        var $modal = $select.closest('.modal');

        if ($repeatable.length && $modal.length) {
            // Wait briefly for Select2 to render the dropdown container
            setTimeout(function() {
                var $openContainer = $('.select2-container--open');
                $openContainer.addClass('select2-in-modal-repeatable');
            }, 0);
        }
    } catch (err) {
        // fail silently
    }
});

$(document).on('select2:close', function(e) {
    try {
        var $select = $(e.target);
        var $repeatable = $select.closest('.repeatable-element');
        var $modal = $select.closest('.modal');

        if ($repeatable.length && $modal.length) {
            // remove the class from any open containers
            $('.select2-container--open').removeClass('select2-in-modal-repeatable');
        }
    } catch (err) {
        // fail silently
    }
});

@endverbatim
