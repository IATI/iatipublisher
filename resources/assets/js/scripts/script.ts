import $ from 'jquery';
import jQuery from 'jquery';

jQuery(function () {
  $('body').on('click', '#hamburger', () => {
    $('#nav-list').toggleClass('nav-active');
    $('#hamburger').toggleClass('active');
    $('body').toggleClass('overflow-hidden');
    $('#menu-overlay').toggleClass('menu-overlay');
    $('#activity-menu-overlay').toggleClass('menu-overlay');
  });

  // close the navMenu by clicking outside
  $('body').on('click', (e) => {
    if (e.target.classList[0] == 'menu-overlay') {
      $('#nav-list').removeClass('nav-active');
      $('#hamburger').removeClass('active');
      $('body').removeClass('overflow-hidden');
      $('#menu-overlay').removeClass('menu-overlay');
      $('#activity-menu-overlay').removeClass('menu-overlay');
    }
  });

  const sidebarBlock = $('.sidebar-help-block');
  $(document).on('click', '.help-button', function () {
    sidebarBlock.removeClass('hidden');
    const sidebarContent = $(this).siblings('.help-button-content').html();
    $('.sidebar-help-block-text').html(sidebarContent);
  });

  $('.sidebar-help-close').on('click', () => {
    sidebarBlock.addClass('hidden');
  });
});

// remove overlay page loader after loading completes
$(window).on('load', function () {
  const overlay = $('.overlay');
  overlay.addClass('hidden');
});

/**
 * Disable submit button after single  click
 */
const submitBtn = 'form button[type="submit"]',
  submitBtnElement = $(submitBtn);

if (submitBtnElement.length > 0) {
  $('body').on('click', submitBtn, function () {
    $(this).attr('disabled', 'disabled');
    $(this).closest('form').trigger('submit');
  });
}

/*
 *
 * Help Text Open Close Handlers Start
 *
 */
$(document).on('click', function (event) {
  if (!$(event.target).closest('.help').length) {
    $('.help__text').removeAttr('style');
  }
});

$(document).on('click', '.help', function (event) {
  event.stopPropagation();

  $('.help__text').removeAttr('style');

  const helpText = $(this).find('.help__text');
  if (helpText.length > 0) {
    helpText.css({
      opacity: '1',
      visibility: 'visible',
    });
  }

  if ($(event.target).closest('.close-help').length) {
    closeHelpText(helpText);
  }
});

$(document).on('keydown', function (event) {
  if (event.key === 'Escape') {
    $('.help__text').each(function () {
      closeHelpText($(this));
    });
  }
});

/**
 * Closes the help text tooltip by setting its CSS properties to make it invisible and non-interactive.
 * After a delay, it removes the inline styles to reset the element's state.
 *
 * @param helpText - The jQuery object representing the tooltip element to be closed.
 */
function closeHelpText(helpText) {
  helpText.css({
    'pointer-events': 'none',
    opacity: '0',
    visibility: 'invisible',
  });

  setTimeout(function () {
    helpText.removeAttr('style');
  }, 1000);
}

/*
 *
 * Help Text Open Close Handlers End
 *
 */
