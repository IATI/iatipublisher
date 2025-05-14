import axios from 'axios';
import $ from 'jquery';
import 'select2';
import { DynamicField } from './DynamicField';
import LanguageService from 'Services/language';

const dynamicField = new DynamicField();
class FormBuilder {
  // adds new collection of sub-element
  public addForm(ev: Event): void {
    ev.preventDefault();
    const target = ev.target as EventTarget;
    const container = $(target).attr('form_type')
      ? $(`.collection-container[form_type ='${$(target).attr('form_type')}']`)
      : $('.collection-container');

    const count = $(target).attr('child_count')
      ? parseInt($(target).attr('child_count') as string) + 1
      : $(target).parent().find('.form-child-body').length;

    const parent_count = $(target).attr('parent_count')
      ? parseInt($(target).attr('parent_count') as string)
      : $(target).parents('.multi-form').index() - 1;

    const wrapper_parent_count = $(target).attr('wrapped_parent_count')
      ? parseInt($(target).attr('wrapped_parent_count') as string)
      : $(target).parents('.wrapped-child-body').index() - 1;

    let proto = container
      .data('prototype')
      .replace(/__PARENT_NAME__/g, parent_count);

    if ($(target).attr('has_child_collection')) {
      proto = proto.replace(/__WRAPPER_NAME__/g, count);
      proto = proto.replace(/__NAME__/g, 0);
    } else {
      proto = proto.replace(/__NAME__/g, count);
      proto = proto.replace(/__WRAPPER_NAME__/g, wrapper_parent_count);
    }

    $(target).prev().append($(proto));
    if ($(target).attr('has_child_collection')) {
      $(target)
        .prev('.subelement')
        .children('.wrapped-child-body')
        .last()
        .find('.add_to_collection')
        .attr('wrapped_parent_count', count);
      $(target)
        .prev('.subelement')
        .children('.wrapped-child-body')
        .last()
        .find('.add_to_collection')
        .attr('parent_count', parent_count);
    }

    $(target)
      .prev()
      .find('.wrapped-child-body')
      .last()
      .find('.add_to_collection')
      .attr('wrapper_parent_count', wrapper_parent_count ?? 0);

    if ($(target).attr('form_type')) {
      $(target).prev().last().find('.select2').select2({
        placeholder: 'Select an option',
        allowClear: true,
      });

      $(this)
        .find('.sub-attribute')
        .wrapAll(
          $(
            '<div class="form-field-group flex flex-wrap sub-attribute-wrapper"></div>'
          )
        );

      $(target)
        .prev('.subelement')
        .children('.wrapped-child-body')
        .last()
        .find('.sub-attribute')
        .wrapAll(
          $(
            '<div class="form-field-group flex flex-wrap sub-attribute-wrapper mt-6"></div>'
          )
        );
    } else {
      $(target)
        .parent()
        .find('.form-child-body')
        .last()
        .find('.select2')
        .select2({
          placeholder: 'Select an option',
          allowClear: true,
        });
    }

    $(target).attr('child_count', count);
    dynamicField.aidTypeVocabularyHideField();
    dynamicField.sectorVocabularyHideField();
  }

  // adds parent collection
  public addParentForm(ev: Event): void {
    ev.preventDefault();
    const target = ev.target as EventTarget;
    const container = $(target).attr('form_type')
      ? $(`.parent-collection[form_type ='${$(target).attr('form_type')}']`)
      : $('.parent-collection');

    const count = $(target).attr('parent_count')
      ? parseInt($(target).attr('parent_count') as string) + 1
      : ($(target).prev().find('.multi-form').length
          ? $(target).prev().find('.multi-form').length
          : $(target).prev().find('.wrapped-child-body').length) + 1;

    let proto = container.data('prototype').replace(/__PARENT_NAME__/g, count);

    proto = proto.replace(/__NAME__/g, 0);

    $(target).prev().append($(proto));
    $(target).prev().find('.multi-form').last().find('.select2').select2({
      placeholder: 'Select an option',
      allowClear: true,
    });
    $(target)
      .prev()
      .find('.multi-form')
      .last()
      .find('.add_to_collection')
      .attr('parent_count', count);

    this.addWrapperOnAdd(target);

    $(target).attr('parent_count', count);

    dynamicField.humanitarianScopeHideVocabularyUri();
    dynamicField.countryBudgetHideCodeField();
    dynamicField.sectorVocabularyHideField();
    dynamicField.recipientVocabularyHideField();
    dynamicField.policyVocabularyHideField();
    dynamicField.tagVocabularyHideField();
    dynamicField.transactionAidTypeVocabularyHideField();
    dynamicField.indicatorReferenceHideFieldUri();
  }

  // deletes collection
  public deleteForm(ev: Event): void {
    ev.preventDefault();
    const target = ev.target as EventTarget;
    const collectionLength = $('.multi-form').length
      ? $(target).closest('.subelement').find('.form-child-body').length
      : $('.form-child-body').length;
    const count = $('.add_to_collection').attr('child_count')
      ? parseInt($('.add_to_collection').attr('child_count') as string) + 1
      : collectionLength;
    $('.add_to_collection').attr('child_count', count);

    if (collectionLength > 1) {
      const tg = $(target).closest('.form-child-body');
      tg.next('.error').remove();
      tg.remove();
    }
  }

  // deletes parent collection
  public deleteParentForm(ev: Event): void {
    ev.preventDefault();
    const target = ev.target as EventTarget;
    const collectionLength = $('.subelement').length;
    const count = $('.add_to_parent').attr('child_count')
      ? parseInt($('.add_to_parent').attr('child_count') as string) + 1
      : collectionLength;
    $('.add_to_parent').attr('child_count', count);
    $('.add_to_parent').attr('parent_count', count);

    if (collectionLength > 2) {
      $(target).parent().remove();
    }
  }

  //add wrapper div around the attributes
  public addWrapper(): void {
    $('.multi-form').each(function () {
      $(this)
        .find('.attribute')
        .wrapAll(
          $(
            '<div class="form-field-group flex flex-wrap attribute-wrapper mb-4"></div>'
          )
        );
    });

    $('.subelement')
      .find('.wrapped-child-body')
      .each(function () {
        $(this)
          .find('.sub-attribute')
          .wrapAll(
            $(
              '<div class="form-field-group flex flex-wrap sub-attribute-wrapper mb-4"></div>'
            )
          );
      });

    const formField = $('form>.form-field');

    if (formField.length > 0) {
      formField.wrapAll(
        '<div class="form-field-group-outer grid xl:grid-cols-2 mb-6 -mx-3 gap-y-6"></div>'
      );
    }
  }

  public addWrapperOnAdd(target: EventTarget): void {
    $(target)
      .prev()
      .find('.multi-form')
      .last()
      .find('.attribute')
      .wrapAll(
        $(
          '<div class="form-field-group grid xl:grid-cols-2 attribute-wrapper mb-4"></div>'
        )
      );

    $(target)
      .prev()
      .find('.multi-form')
      .last()
      .find('.subelement')
      .find('.wrapped-child-body')
      .each(function () {
        $(this)
          .find('.sub-attribute')
          .wrapAll(
            $(
              '<div class="form-field-group flex flex-wrap sub-attribute-wrapper mb-4"></div>'
            )
          );
      });
  }

  public textAreaHeight(ev: Event) {
    const target = ev.target as HTMLElement;
    const height = target.scrollHeight;
    $(target).css('height', height);
  }

  public addToCollection() {
    $('body').on('click', '.add_to_collection', (event: Event) => {
      if ($(event.target as EventTarget).hasClass('add-icon')) {
        event.stopPropagation();
        $(event.target as EventTarget)
          .parent('button')
          .trigger('click');
      } else {
        this.addForm(event);
        this.handleDeleteParentButtons();
      }
    });

    $('.add_to_parent').on('click', (event: Event) => {
      if ($(event.target as EventTarget).hasClass('add-icon')) {
        event.stopPropagation();
        $(event.target as EventTarget)
          .parent('button')
          .trigger('click');
      } else {
        this.addParentForm(event);
        this.handleDeleteParentButtons();
      }
    });
  }

  public deleteCollection() {
    const deleteConfirmation = $('.delete-confirmation'),
      cancelPopup = '.cancel-popup',
      deleteConfirm = '.delete-confirm';
    let deleteIndex = {},
      childOrParent = '';

    $('body').on('click', '.delete', (event: Event) => {
      deleteConfirmation.fadeIn();
      deleteIndex = event;
      childOrParent = 'child';
    });

    $('body').on('click', cancelPopup, () => {
      deleteConfirmation.fadeOut();
      deleteIndex = {};
      childOrParent = '';
    });

    $('body').on('click', deleteConfirm, () => {
      if (childOrParent === 'child') {
        this.deleteForm(deleteIndex as Event);
      } else if (childOrParent === 'parent') {
        this.deleteParentForm(deleteIndex as Event);
      }

      deleteConfirmation.fadeOut();
      deleteIndex = {};
      childOrParent = '';
    });

    $('body').on('mouseenter', '.delete-parent', (event: Event) => {
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      //@ts-ignore
      const deleteButton = $(event.target);
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      //@ts-ignore
      const multiForm = deleteButton.closest(
        '.multi-form, .wrapped-child-body'
      );

      multiForm.css({
        background: '#FFF8F7',
        outline: '2px solid #F19BA0',
      });
    });

    $('body').on('mouseleave', '.delete-parent', (event: Event) => {
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      //@ts-ignore
      const deleteButton = $(event.target);
      // eslint-disable-next-line @typescript-eslint/ban-ts-comment
      //@ts-ignore
      const multiForm = deleteButton.closest(
        '.multi-form, .wrapped-child-body'
      );

      multiForm.css({
        background: '',
        outline: '',
      });
    });

    $('body').on('click', '.delete-parent', (event: Event) => {
      deleteConfirmation.fadeIn();
      deleteIndex = event;
      childOrParent = 'parent';
    });

    $('.select2').select2({
      placeholder: 'Select an option',
      allowClear: true,
    });

    // update format on change of document link
    $('body').on('change', 'input[id*="[url]"]', function () {
      const filePath = ($(this).val() ?? '').toString();
      const document = $(this)
        .closest('.form-field-group')
        .find('input[id*="[document]"]')
        .val();
      const url = `/mimetype?url=${filePath}&type=url`;
      $(this).closest('.form-field').find('.text-danger').remove();

      if (filePath !== '') {
        axios.get(url).then((response) => {
          if (response.data.success) {
            const format = response.data.data.mimetype;
            $(this)
              .closest('.form-field-group')
              .find('select[id*="[format]"]')
              .val(format)
              .trigger('change');
          } else {
            $(this).closest('.form-field').find('.text-danger').remove();
            $(this)
              .closest('.form-field')
              .append(
                "<div class='text-danger error'>" +
                  response.data.message +
                  '</div>'
              );

            $(this)
              .closest('.form-field-group')
              .find('select[id*="[format]"]')
              .val('')
              .trigger('change');
          }
          $(this)
            .closest('.form-field-group')
            .find('input[id*="[document]"]')
            .val('')
            .trigger('change');
        });
      } else if (!document || document === '') {
        $(this)
          .closest('.form-field-group')
          .find('select[id*="[format]"]')
          .val('')
          .trigger('change');
      }
    });

    $('body').on('change', 'input[id*="[document]"]', function () {
      const filePath = ($(this).val() ?? '').toString();
      const url = `/mimetype?url=${filePath}&&type=document`;
      const fileUrl = $(this)
        .closest('.form-field-group')
        .find('input[id*="[url]"]')
        .val();

      $(this).closest('.form-field').find('.text-danger').remove();

      if (filePath !== '') {
        axios.get(url).then((response) => {
          if (response.data.success) {
            const format = response.data.data.mimetype;
            $(this)
              .closest('.form-field-group')
              .find('select[id*="[format]"]')
              .val(format)
              .trigger('change');
          } else {
            $(this)
              .closest('.form-field-group')
              .find('select[id*="[format]"]')
              .val('')
              .trigger('change');
          }
        });
        $(this)
          .closest('.form-field-group')
          .find('input[id*="[url]"]')
          .val('')
          .trigger('change');
      } else if (!fileUrl || fileUrl === '') {
        $(this)
          .closest('.form-field-group')
          .find('select[id*="[format]"]')
          .val('')
          .trigger('change');
      }
    });
  }

  public handleDeleteParentButtons() {
    const deleteButtons = document.querySelectorAll('.delete-parent-selector');
    const changeDeleteButtonInnerHtml = (button) => {
      const initialText = escapeHtml(button.textContent);
      button.innerHTML = `
         <svg class="text-[1rem] mb-0.5" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
           <path d="M6.66667 12C6.84348 12 7.01305 11.9298 7.13807 11.8047C7.2631 11.6797 7.33333 11.5101 7.33333 11.3333V7.33334C7.33333 7.15653 7.2631 6.98696 7.13807 6.86193C7.01305 6.73691 6.84348 6.66667 6.66667 6.66667C6.48986 6.66667 6.32029 6.73691 6.19526 6.86193C6.07024 6.98696 6 7.15653 6 7.33334V11.3333C6 11.5101 6.07024 11.6797 6.19526 11.8047C6.32029 11.9298 6.48986 12 6.66667 12ZM13.3333 4H10.6667V3.33334C10.6667 2.8029 10.456 2.2942 10.0809 1.91912C9.70581 1.54405 9.1971 1.33334 8.66667 1.33334H7.33333C6.8029 1.33334 6.29419 1.54405 5.91912 1.91912C5.54405 2.2942 5.33333 2.8029 5.33333 3.33334V4H2.66667C2.48986 4 2.32029 4.07024 2.19526 4.19526C2.07024 4.32029 2 4.48986 2 4.66667C2 4.84348 2.07024 5.01305 2.19526 5.13807C2.32029 5.2631 2.48986 5.33334 2.66667 5.33334H3.33333V12.6667C3.33333 13.1971 3.54405 13.7058 3.91912 14.0809C4.29419 14.456 4.8029 14.6667 5.33333 14.6667H10.6667C11.1971 14.6667 11.7058 14.456 12.0809 14.0809C12.456 13.7058 12.6667 13.1971 12.6667 12.6667V5.33334H13.3333C13.5101 5.33334 13.6797 5.2631 13.8047 5.13807C13.9298 5.01305 14 4.84348 14 4.66667C14 4.48986 13.9298 4.32029 13.8047 4.19526C13.6797 4.07024 13.5101 4 13.3333 4ZM6.66667 3.33334C6.66667 3.15652 6.7369 2.98696 6.86193 2.86193C6.98695 2.73691 7.15652 2.66667 7.33333 2.66667H8.66667C8.84348 2.66667 9.01305 2.73691 9.13807 2.86193C9.2631 2.98696 9.33333 3.15652 9.33333 3.33334V4H6.66667V3.33334ZM11.3333 12.6667C11.3333 12.8435 11.2631 13.0131 11.1381 13.1381C11.013 13.2631 10.8435 13.3333 10.6667 13.3333H5.33333C5.15652 13.3333 4.98695 13.2631 4.86193 13.1381C4.7369 13.0131 4.66667 12.8435 4.66667 12.6667V5.33334H11.3333V12.6667ZM9.33333 12C9.51014 12 9.67971 11.9298 9.80474 11.8047C9.92976 11.6797 10 11.5101 10 11.3333V7.33334C10 7.15653 9.92976 6.98696 9.80474 6.86193C9.67971 6.73691 9.51014 6.66667 9.33333 6.66667C9.15652 6.66667 8.98695 6.73691 8.86193 6.86193C8.73691 6.98696 8.66667 7.15653 8.66667 7.33334V11.3333C8.66667 11.5101 8.73691 11.6797 8.86193 11.8047C8.98695 11.9298 9.15652 12 9.33333 12Z" fill="#E34D5B"/>
         </svg>
         ${initialText}
      `;
    };

    deleteButtons.forEach((button) => {
      changeDeleteButtonInnerHtml(button);
    });
  }
}

$(async function () {
  const currentLanguage = await LanguageService.getLanguage();
  const formBuilder = new FormBuilder();

  formBuilder.addWrapper();
  dynamicField.hideShowFormFields();
  dynamicField.updateActivityIdentifier();
  formBuilder.addToCollection();
  formBuilder.deleteCollection();

  /**
   * Text area height on typing
   */
  const textAreaTarget = $('textarea.form__input');
  if (textAreaTarget.length > 0) {
    $('body').on('input', 'textarea.form__input', (event: Event) => {
      formBuilder.textAreaHeight(event);
    });
  }

  $('body').on('select2:open', '.select2', () => {
    const select_search = document.querySelector(
      '.select2-search__field'
    ) as HTMLElement;

    if (select_search) {
      select_search.focus();
    }
  });

  /**
   * checks registration agency, country and registration number to deduce identifier
   */
  updateRegistrationAgency($('#organization_country'));
  $('#organisation_identifier').attr('disabled', 'disabled');

  function updateRegistrationAgency(country: JQuery) {
    const endpoint = country.val()
      ? '/organisation/agency/' + country.val()
      : '/organisation/agency/';
    $.ajax({ url: endpoint }).then((response) => {
      const current_val = $('#organization_registration_agency').val() ?? '';
      let val = false;

      $('#organization_registration_agency').empty();

      for (const data in response.data) {
        if (data === current_val) {
          val = true;
        }

        $('#organization_registration_agency')
          .append(new Option(response.data[data], data, true, true))
          .val('')
          .trigger('change');
      }

      $('#organization_registration_agency')
        .val(val ? current_val : '')
        .trigger('change');
    });
  }

  $('body').on('select2:select', '#organization_country', function () {
    updateRegistrationAgency($(this));
  });
  $('body').on('select2:clear', '#organization_country', function () {
    updateRegistrationAgency($(this));
  });

  $('body').on(
    'select2:select',
    '#organization_registration_agency',
    function () {
      const identifier = $(this).val() + '-' + $('#registration_number').val();
      $('#organisation_identifier').val(identifier);
    }
  );

  $('body').on(
    'select2:clear',
    '#organization_registration_agency',
    function () {
      const identifier = '-' + $('#registration_number').val();
      $('#organisation_identifier').val(identifier);
    }
  );

  $('body').on('keyup', '#registration_number', function () {
    const identifier =
      $('#organization_registration_agency').val() + '-' + $(this).val();
    $('#organisation_identifier').val(identifier);
  });

  // add class to title of collection when validation error occurs on collection level
  const subelement = document.querySelectorAll('.subelement');

  for (let i = 0; i < subelement.length; i++) {
    const title = subelement[i].querySelector('.control-label');
    const errorContainer = subelement[i].querySelector('.collection_error');
    const childCount = errorContainer?.childElementCount;

    if (childCount && childCount > 0) {
      title?.classList.add('error-title');
    }
  }

  // Adding cursor not allowed to <select> where elementJsonSchema read_only : true
  const readOnlySelects = document.querySelectorAll(
    'select.cursor-not-allowed'
  );
  for (let i = 0; i < readOnlySelects.length; i++) {
    const select = readOnlySelects[i];
    const selectElementParentWrapper = select.nextSibling;
    const selectElementParent = selectElementParentWrapper?.firstChild;
    const selectElement = selectElementParent?.firstChild as HTMLElement;
    if (selectElement) {
      selectElement.style.cursor = 'not-allowed';
    }
  }

  const deleteButtons = document.querySelectorAll('.delete-parent-selector');

  function changeDeleteButtonInnerHtml(button) {
    const initialText = escapeHtml(button.textContent);
    button.innerHTML = `
      <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6.66667 12C6.84348 12 7.01305 11.9298 7.13807 11.8047C7.2631 11.6797 7.33333 11.5101 7.33333 11.3333V7.33334C7.33333 7.15653 7.2631 6.98696 7.13807 6.86193C7.01305 6.73691 6.84348 6.66667 6.66667 6.66667C6.48986 6.66667 6.32029 6.73691 6.19526 6.86193C6.07024 6.98696 6 7.15653 6 7.33334V11.3333C6 11.5101 6.07024 11.6797 6.19526 11.8047C6.32029 11.9298 6.48986 12 6.66667 12ZM13.3333 4H10.6667V3.33334C10.6667 2.8029 10.456 2.2942 10.0809 1.91912C9.70581 1.54405 9.1971 1.33334 8.66667 1.33334H7.33333C6.8029 1.33334 6.29419 1.54405 5.91912 1.91912C5.54405 2.2942 5.33333 2.8029 5.33333 3.33334V4H2.66667C2.48986 4 2.32029 4.07024 2.19526 4.19526C2.07024 4.32029 2 4.48986 2 4.66667C2 4.84348 2.07024 5.01305 2.19526 5.13807C2.32029 5.2631 2.48986 5.33334 2.66667 5.33334H3.33333V12.6667C3.33333 13.1971 3.54405 13.7058 3.91912 14.0809C4.29419 14.456 4.8029 14.6667 5.33333 14.6667H10.6667C11.1971 14.6667 11.7058 14.456 12.0809 14.0809C12.456 13.7058 12.6667 13.1971 12.6667 12.6667V5.33334H13.3333C13.5101 5.33334 13.6797 5.2631 13.8047 5.13807C13.9298 5.01305 14 4.84348 14 4.66667C14 4.48986 13.9298 4.32029 13.8047 4.19526C13.6797 4.07024 13.5101 4 13.3333 4ZM6.66667 3.33334C6.66667 3.15652 6.7369 2.98696 6.86193 2.86193C6.98695 2.73691 7.15652 2.66667 7.33333 2.66667H8.66667C8.84348 2.66667 9.01305 2.73691 9.13807 2.86193C9.2631 2.98696 9.33333 3.15652 9.33333 3.33334V4H6.66667V3.33334ZM11.3333 12.6667C11.3333 12.8435 11.2631 13.0131 11.1381 13.1381C11.013 13.2631 10.8435 13.3333 10.6667 13.3333H5.33333C5.15652 13.3333 4.98695 13.2631 4.86193 13.1381C4.7369 13.0131 4.66667 12.8435 4.66667 12.6667V5.33334H11.3333V12.6667ZM9.33333 12C9.51014 12 9.67971 11.9298 9.80474 11.8047C9.92976 11.6797 10 11.5101 10 11.3333V7.33334C10 7.15653 9.92976 6.98696 9.80474 6.86193C9.67971 6.73691 9.51014 6.66667 9.33333 6.66667C9.15652 6.66667 8.98695 6.73691 8.86193 6.86193C8.73691 6.98696 8.66667 7.15653 8.66667 7.33334V11.3333C8.66667 11.5101 8.73691 11.6797 8.86193 11.8047C8.98695 11.9298 9.15652 12 9.33333 12Z" fill="#E34D5B"/>
      </svg>
      ${initialText}`;
  }

  deleteButtons.forEach((button) => changeDeleteButtonInnerHtml(button));

  const observer = new MutationObserver((mutationsList) => {
    mutationsList.forEach((mutation) => {
      if (mutation.addedNodes.length > 0) {
        mutation.addedNodes.forEach((node) => {
          if (node instanceof Element) {
            if (node.matches('.delete-item-selector')) {
              changeDeleteButtonInnerHtml(node);
            } else {
              const newDeleteButtons = node.querySelectorAll(
                '.delete-item-selector'
              );
              newDeleteButtons.forEach((button) =>
                changeDeleteButtonInnerHtml(button)
              );
            }
          }
        });
      }
    });
  });

  observer.observe(document.body, {
    childList: true,
    subtree: true,
  });

  /**
   * This function does two main things:
   *
   * 1. Adds a click event listener to the button to control the collapsible flow:
   *    - It finds the closest <label> element related to the button.
   *    - Within that <label>, it looks for an element with the class 'optional-text'. If it finds 'optional-text', it toggles how that text is displayed (either with brackets or an icon).
   *    - It also locates the nearest parent element with the classes 'subelement rounded-t-sm'. If that parent subelement exists, it toggles its state to either collapse or expand the form section.
   *    - Finally, it rotates the collapse button each time it’s clicked.
   *
   * 2. It triggers the button click event if the subelement is optional using the flag: thisButtonBelongsToOptionalForm.
   *    This ensures optional forms start off collapsed by default when rendered.
   *
   * @param button - The button element that manages the collapsible form section.
   */
  function attachCollapsableButtonEvents(
    button: HTMLButtonElement,
    currentLanguage: string
  ) {
    const label = getClosestLabelDom(button);
    const optionalLabel = label ? getOptionalTextDom(label) : null;
    const subelement = label ? getClosestParentSubelementDom(label) : null;

    const thisButtonBelongsToOptionalForm = optionalLabel !== null;

    button.addEventListener('click', () => {
      if (optionalLabel) {
        toggleOptionalText(optionalLabel, currentLanguage);
      }

      if (subelement) {
        toggleAccordionItems(subelement);
      }

      button.classList.toggle('rotate-180');
    });

    if (thisButtonBelongsToOptionalForm && !errorMessageExists(subelement)) {
      button.click();
    }
  }

  /**
   * Check if any error message exists in the subelement.
   *
   * @param subelement
   */
  function errorMessageExists(subelement) {
    const errorDivs = subelement.querySelectorAll('.error');
    const errorTexts = subelement.querySelectorAll('.text-danger-error');

    for (const div of errorDivs) {
      if (div.textContent.trim() !== '') {
        return true;
      }
    }

    for (const div of errorTexts) {
      if (div.textContent.trim() !== '') {
        return true;
      }
    }

    return false;
  }

  /**
   * Returns closest <label> element.
   *
   * @param button
   */
  function getClosestLabelDom(button): HTMLElement | null {
    return button.closest('label') as HTMLLabelElement | null;
  }

  /**
   * Returns closest element with class 'optional-text'.
   *
   * @param label
   */
  function getOptionalTextDom(label: HTMLElement) {
    return label.querySelector('.optional-text');
  }

  /**
   * Returns the first Nth parent that has class 'subelement'.
   *
   * @param label
   */
  function getClosestParentSubelementDom(label: HTMLElement) {
    return label.closest('.subelement.rounded-t-sm') as HTMLElement | null;
  }

  /**
   * Toggles what is rendered on optional text. (dot or bracket)
   *
   * @param optionalLabel
   * @param currentLanguage
   */
  function toggleOptionalText(optionalLabel: Element, currentLanguage = 'en') {
    let optionalLabelString = 'Optional';

    if (currentLanguage === 'fr') {
      optionalLabelString = 'Opcional';
    } else if (currentLanguage === 'es') {
      optionalLabelString = 'Facultatif';
    }

    const optionalLabelWithSvg = `<svg viewBox="0 0 16 18" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M6 9a1.87 1.87 0 1 0 3.74 0A1.87 1.87 0 0 0 6 9Z" fill="#68797E"></path>
        </svg>
        <span>
          ${optionalLabelString}
        </span>`;

    const optionalLabelWithBrackets = `<span>( ${optionalLabelString} )</span>`;

    const svgExists = optionalLabel.querySelector('svg') !== null;

    if (svgExists) {
      optionalLabel.innerHTML = optionalLabelWithBrackets;
    } else {
      optionalLabel.innerHTML = optionalLabelWithSvg;
    }
  }

  /**
   * Toggles collapsed state. (expand or collapsed)
   *
   * Key considerations:
   * 1. The "Add Additional" button can be either inside or outside the subelement.
   * 2. When the button is outside, it will always be the immediate sibling to the subelement.
   * 3. The collapse mechanism is handled by adjusting the max height to give the illusion of sliding up.
   * 4. If the button is outside the subelement, the slide-up effect will not affect the button.
   *    Therefore, we toggle the 'display-none' class to control its visibility.
   *
   * @param subelement
   */
  function toggleAccordionItems(subelement) {
    function isAddAdditionalButtonOutside(subelement) {
      const nextSibling = subelement.nextElementSibling;

      if (nextSibling && nextSibling.tagName === 'BUTTON') {
        return (
          nextSibling.classList.contains('add_more') &&
          nextSibling.classList.contains('button')
        );
      }

      return false;
    }

    const hideableSubelements = [...subelement.children].filter(
      (child) => child.tagName !== 'LABEL'
    );

    let outsideButton: null | Element = null;
    const hasAddAdditionalButtonOutside =
      isAddAdditionalButtonOutside(subelement);

    if (hasAddAdditionalButtonOutside) {
      outsideButton = subelement.nextElementSibling;
      if (outsideButton) {
        outsideButton.classList.toggle('display-none');
      }
    }

    hideableSubelements.forEach((child) => {
      if (hasAddAdditionalButtonOutside && outsideButton) {
        subelement.classList.toggle('mb-6');
      }

      if (child.classList.contains('height-hide')) {
        child.classList.remove('height-hide');
        child.classList.add('height-show');
      } else {
        child.classList.remove('height-show');
        child.classList.add('height-hide');
      }
    });
  }

  /**
   * This function handles the forms rendered on initial page load.
   */
  function attachInitialCollapsableButtonEvents(currentLanguage: string) {
    const allCollapsableButtons = document.querySelectorAll<HTMLButtonElement>(
      '.collapsable-button'
    );
    allCollapsableButtons.forEach((button) =>
      attachCollapsableButtonEvents(button, currentLanguage)
    );
  }

  /**
   * This function handles the forms rendered on clicking 'ADD ADDITIONAL X' button.
   */
  function observeNewCollapsableButtons(currentLanguage: string) {
    const observer = new MutationObserver((mutationsList) => {
      mutationsList.forEach((mutation) => {
        if (mutation.type === 'childList') {
          mutation.addedNodes.forEach((node) => {
            if (node instanceof HTMLElement) {
              const newCollapsableButtons =
                node.querySelectorAll<HTMLButtonElement>('.collapsable-button');
              newCollapsableButtons.forEach((button) =>
                attachCollapsableButtonEvents(button, currentLanguage)
              );
            }
          });
        }
      });
    });

    observer.observe(document.body, { childList: true, subtree: true });
  }

  attachInitialCollapsableButtonEvents(currentLanguage);
  observeNewCollapsableButtons(currentLanguage);
});

function escapeHtml(unsafe: string) {
  return unsafe
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
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
  console.log('Hello');

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
