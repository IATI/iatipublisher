import $ from 'jquery';
import 'select2';

export class DynamicField {
  /**
   * Hide and Show different form fields based on vocabulary and other types
   */
  public hideShowFormFields() {
    this.humanitarianScopeHideVocabularyUri();
    this.countryBudgetHideCodeField();
    this.aidTypeVocabularyHideField();
    this.sectorVocabularyHideField();
    this.policyVocabularyHideField();
    this.recipientVocabularyHideField();
    this.sectorVocabularyHideField();
    this.tagVocabularyHideField();
    this.transactionAidTypeVocabularyHideField();
    this.indicatorReferenceHideFieldUri();
  }

  /**
   * Humanitarian Scope Form Page
   *
   * @Logic hide vocabulary-uri field based on '@vocabulary' field value
   */
  public humanitarianScopeHideVocabularyUri() {
    const humanitarianScopeVocabulary = $(
      'select[id^="humanitarian_scope"][id*="[vocabulary]"]'
    );

    if (humanitarianScopeVocabulary.length > 0) {
      // hide fields on page load
      $.each(humanitarianScopeVocabulary, (index, scope) => {
        const val = $(scope).val() ?? '';
        this.hideHumanitarianScopeField($(scope), val.toString());
      });

      // hide/show fields on value change
      humanitarianScopeVocabulary.on('select2:select', (e) => {
        const val = e.params.data.id;
        const index = e.target as HTMLElement;

        this.hideHumanitarianScopeField($(index), val);
      });

      // hide/show fields on value clear
      humanitarianScopeVocabulary.on('select2:clear', (e) => {
        const index = e.target as HTMLElement;

        this.hideHumanitarianScopeField($(index), '');
      });
    }
  }

  // hide country budget based on vocabulary
  public hideHumanitarianScopeField(index: JQuery, value: string) {
    const humanitarianScopeHideVocabularyUri =
      'input[id^="humanitarian_scope"][id*="[vocabulary_uri]"]';

    if (value === '99') {
      index
        .closest('.form-field-group')
        .find(humanitarianScopeHideVocabularyUri)
        .show()
        .removeAttr('disabled')
        .closest('.form-field')
        .show();
    } else {
      index
        .closest('.form-field-group')
        .find(humanitarianScopeHideVocabularyUri)
        .val('')
        .trigger('change')
        .hide()
        .attr('disabled', 'disabled')
        .closest('.form-field')
        .hide();
    }
  }

  /**
   * Humanitarian Scope Form Page
   *
   * @Logic hide vocabulary-uri field based on '@vocabulary' field value
   */
  public indicatorReferenceHideFieldUri() {
    const referenceVocabulary = $(
      'select[id^="reference"][id*="[vocabulary]"]'
    );

    if (referenceVocabulary.length > 0) {
      // hide fields on page load
      $.each(referenceVocabulary, (index, scope) => {
        const val = $(scope).val() ?? '';
        this.indicatorReferenceHideField($(scope), val.toString());
      });

      // hide/show fields on value change
      referenceVocabulary.on('select2:select', (e) => {
        const val = e.params.data.id;
        const index = e.target as HTMLElement;

        this.indicatorReferenceHideField($(index), val);
      });

      // hide/show fields on value clear
      referenceVocabulary.on('select2:clear', (e) => {
        const index = e.target as HTMLElement;

        this.indicatorReferenceHideField($(index), '');
      });
    }
  }

  // hide country budget based on vocabulary
  public indicatorReferenceHideField(index: JQuery, value: string) {
    const referenceUri = 'input[id^="reference"][id*="[indicator_uri]"]';

    if (value === '99') {
      index
        .closest('.form-field-group')
        .find(referenceUri)
        .show()
        .removeAttr('disabled')
        .closest('.form-field')
        .show();
    } else {
      index
        .closest('.form-field-group')
        .find(referenceUri)
        .val('')
        .trigger('change')
        .hide()
        .attr('disabled', 'disabled')
        .closest('.form-field')
        .hide();
    }
  }

  /**
   * Country Budget Form Page
   *
   * @Logic show/hide 'code' field based on '@vocabulary' field value
   */
  public countryBudgetHideCodeField() {
    const countryBudgetVocabulary = $('select#country_budget_vocabulary');

    if (countryBudgetVocabulary.length > 0) {
      // hide/show on page load
      const val = countryBudgetVocabulary.val() ?? '1';
      this.hideCountryBudgetField(val.toString());

      // hide/show on value change
      countryBudgetVocabulary.on('select2:select', (e) => {
        const val = e.params.data.id;
        this.hideCountryBudgetField(val);
      });

      //hide/show based on value cleared
      countryBudgetVocabulary.on('select2:clear', () => {
        this.hideCountryBudgetField('');
      });
    }
  }

  /**
   * Hide Country Budget Fields
   */
  public hideCountryBudgetField(value: string) {
    const countryBudgetCodeInput =
        'input[id^="budget_item"][id*="[code_text]"]',
      countryBudgetCodeSelect = 'select[id^="budget_item"][id*="[code]"]';

    if (value === '1') {
      $(countryBudgetCodeSelect)
        .val('')
        .trigger('change')
        .attr('disabled', 'disabled')
        .closest('.form-field')
        .hide();
      $(countryBudgetCodeInput)
        .removeAttr('disabled')
        .closest('.form-field')
        .show();
    } else {
      $(countryBudgetCodeSelect)
        .removeAttr('disabled')
        .closest('.form-field')
        .show();
      $(countryBudgetCodeInput)
        .val('')
        .trigger('change')
        .closest('.form-field')
        .hide();
    }
  }

  /**
   * AidType Form Page
   *
   * @Logic hide vocabulary-uri and codes field based on '@vocabulary' field value
   */
  public aidTypeVocabularyHideField() {
    const aidtype_vocabulary = $('select[id*="default_aid_type_vocabulary"]');

    if (aidtype_vocabulary.length > 0) {
      $.each(aidtype_vocabulary, (index, item) => {
        const data = $(item).val() ?? '1';
        this.hideAidTypeSelectField($(item), data.toString());
      });

      aidtype_vocabulary.on('select2:select', (e) => {
        const data = e.params.data.id;
        const target = e.target as HTMLElement;

        this.hideAidTypeSelectField($(target), data);
      });

      aidtype_vocabulary.on('select2:clear', (e) => {
        const target = e.target as HTMLElement;

        this.hideAidTypeSelectField($(target), '');
      });
    }
  }

  /**
   * AidType Form Page
   *
   * @Logic hide vocabulary-uri and codes field based on '@vocabulary' field value
   */
  public transactionAidTypeVocabularyHideField() {
    const aidtype_vocabulary = $('select[id*="aid_type_vocabulary"]');

    if (aidtype_vocabulary.length > 0) {
      $.each(aidtype_vocabulary, (index, item) => {
        const data = $(item).val() ?? '1';
        this.hideTransactionAidTypeSelectField($(item), data.toString());
      });

      aidtype_vocabulary.on('select2:select', (e) => {
        const data = e.params.data.id;
        const target = e.target as HTMLElement;

        this.hideTransactionAidTypeSelectField($(target), data);
      });

      aidtype_vocabulary.on('select2:clear', (e) => {
        const target = e.target as HTMLElement;

        this.hideTransactionAidTypeSelectField($(target), '');
      });
    }
  }

  /**
   * Hide Aid Type Select Fields
   */
  public hideAidTypeSelectField(index: JQuery, value: string) {
    const default_aid_type = 'select[id*="[default_aid_type]"]',
      earmarking_category = 'select[id*="[earmarking_category]"]',
      earmarking_modality = 'select[id*="[earmarking_modality]"]',
      cash_and_voucher_modalities =
        'select[id*="[cash_and_voucher_modalities]"]',
      case1 =
        'select[id*="[earmarking_category]"],select[id*="[earmarking_modality]"],select[id*="[cash_and_voucher_modalities]"]',
      case2 =
        'select[id*="[default_aid_type]"],select[id*="[earmarking_modality]"],select[id*="[cash_and_voucher_modalities]"]',
      case3 =
        'select[id*="[default_aid_type]"],select[id*="[earmarking_category]"],select[id*="[cash_and_voucher_modalities]"]',
      case4 =
        'select[id*="[default_aid_type]"],select[id*="[earmarking_category]"],select[id*="[earmarking_modality]"]';

    switch (value) {
      case '2':
        index
          .closest('.form-field-group')
          .find(earmarking_category)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case2)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '3':
        index
          .closest('.form-field-group')
          .find(earmarking_modality)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case3)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;

      case '4':
        index
          .closest('.form-field-group')
          .find(cash_and_voucher_modalities)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case4)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      default:
        index
          .closest('.form-field-group')
          .find(default_aid_type)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case1)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
    }
  }

  /**
   * Hide Transaction Aid Type Select Fields
   */
  public hideTransactionAidTypeSelectField(index: JQuery, value: string) {
    const aid_type = 'select[id*="[aid_type_code]"]',
      earmarking_category = 'select[id*="[earmarking_category]"]',
      earmarking_modality = 'select[id*="[earmarking_modality]"]',
      cash_and_voucher_modalities =
        'select[id*="[cash_and_voucher_modalities]"]',
      case1 =
        'select[id*="[earmarking_category]"],select[id*="[earmarking_modality]"],select[id*="[cash_and_voucher_modalities]"]',
      case2 =
        'select[id*="[aid_type_code]"],select[id*="[earmarking_modality]"],select[id*="[cash_and_voucher_modalities]"]',
      case3 =
        'select[id*="[aid_type_code]"],select[id*="[earmarking_category]"],select[id*="[cash_and_voucher_modalities]"]',
      case4 =
        'select[id*="[aid_type_code]"],select[id*="[earmarking_category]"],select[id*="[earmarking_modality]"]';

    switch (value) {
      case '2':
        index
          .closest('.form-field-group')
          .find(earmarking_category)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case2)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '3':
        index
          .closest('.form-field-group')
          .find(earmarking_modality)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case3)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;

      case '4':
        index
          .closest('.form-field-group')
          .find(cash_and_voucher_modalities)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case4)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      default:
        index
          .closest('.form-field-group')
          .find(aid_type)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case1)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
    }
  }

  /**
   * Policy Marker Form Page
   *
   * @Logic hide vocabulary-uri and codes field based on '@vocabulary' field value
   */
  public policyVocabularyHideField() {
    const policymaker_vocabulary = $('select[id*="policy_marker_vocabulary"]');

    if (policymaker_vocabulary.length > 0) {
      $.each(policymaker_vocabulary, (index, policy_marker) => {
        const data = $(policy_marker).val() ?? '1';
        this.hidePolicyMakerField($(policy_marker), data.toString());
      });

      policymaker_vocabulary.on('select2:select', (e) => {
        const data = e.params.data.id;
        const target = e.target as HTMLElement;

        this.hidePolicyMakerField($(target), data);
      });

      policymaker_vocabulary.on('select2:clear', (e) => {
        const target = e.target as HTMLElement;
        this.hidePolicyMakerField($(target), '99');
      });
    }
  }

  /**
   * Hides Policy Marker Form Fields
   */
  public hidePolicyMakerField(index: JQuery, value: string) {
    const case1_show = 'select[id*="[policy_marker]"]',
      case2_show =
        'input[id*="[policy_marker_text]"],input[id*="[vocabulary_uri]"]',
      case1 = 'input[id*="[policy_marker_text]"],input[id*="[vocabulary_uri]"]',
      case2 = 'select[id*="[policy_marker]"]';

    switch (value) {
      case '1':
        index
          .closest('.form-field-group')
          .find(case1_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case1)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '99':
      default:
        index
          .closest('.form-field-group')
          .find(case2_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case2)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
    }
  }

  /**
   * Sector Form Page
   *
   * @Logic hide vocabulary-uri and codes field based on '@vocabulary' field value
   */
  public sectorVocabularyHideField() {
    const sector_vocabulary = $('select[id*="sector_vocabulary"]');

    if (sector_vocabulary.length > 0) {
      $.each(sector_vocabulary, (index, sector) => {
        const data = $(sector).val() ?? '1';
        this.hideSectorField($(sector), data.toString());
      });

      sector_vocabulary.on('select2:select', (e) => {
        const data = e.params.data.id;
        const target = e.target as HTMLElement;

        this.hideSectorField($(target), data);
      });

      sector_vocabulary.on('select2:clear', (e) => {
        const target = e.target as HTMLElement;

        this.hideSectorField($(target), '');
      });
    }
  }

  /**
   * Hide Sector Form fields
   */
  public hideSectorField(index: JQuery, value: string) {
    const case1_show = 'select[id*="[code]"]',
      case2_show = 'select[id*="[category_code]"]',
      case7_show = 'select[id*="[sdg_goal]"]',
      case8_show = 'select[id*="[sdg_target]"]',
      case98_99_show = 'input[id*="[text]"],input[id*="[vocabulary_uri]"]',
      default_show = 'input[id*="[text]"]',
      case1 =
        'select[id*="[category_code]"],select[id*="[sdg_goal]"],select[id*="[sdg_target]"],input[id*="[vocabulary_uri]"],input[id*="[text]"]',
      case2 =
        'input[id*="[vocabulary_uri]"],select[id*="[sdg_goal]"],select[id*="[sdg_target]"],select[id*="[code]"],input[id*="[text]"]',
      case7 =
        'input[id*="[vocabulary_uri]"],select[id*="[category_code]"],select[id*="[sdg_target]"],select[id*="[code]"],input[id*="[text]"]',
      case8 =
        'input[id*="[vocabulary_uri]"],select[id*="[category_code]"],select[id*="[sdg_goal]"],select[id*="[code]"],input[id*="[text]"]',
      case98_99 =
        'select[id*="[category_code]"],select[id*="[sdg_goal]"],select[id*="[sdg_target]"],select[id*="[code]"]',
      default_hide =
        'select[id*="[category_code]"],select[id*="[sdg_goal]"],select[id*="[sdg_target]"],select[id*="[code]"],input[id*="[vocabulary_uri]"]';

    switch (value) {
      case '1':
        index
          .closest('.form-field-group')
          .find(case1_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case1)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '2':
        index
          .closest('.form-field-group')
          .find(case2_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case2)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '7':
        index
          .closest('.form-field-group')
          .find(case7_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case7)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '8':
        index
          .closest('.form-field-group')
          .find(case8_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case8)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '98':
        index
          .closest('.form-field-group')
          .find(case98_99_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case98_99)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '99':
        index
          .closest('.form-field-group')
          .find(case98_99_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case98_99)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      default:
        index
          .closest('.form-field-group')
          .find(default_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();

        index
          .closest('.form-field-group')
          .find(default_hide)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
    }
  }

  /**
   *  Recipient Vocabulary Form Page
   *
   * @Logic hide vocabulary-uri and codes field based on '@vocabulary' field value
   */
  public recipientVocabularyHideField() {
    const region_vocabulary = $('select[id*="region_vocabulary"]');

    if (region_vocabulary.length > 0) {
      $.each(region_vocabulary, (index, region_vocab) => {
        const data = $(region_vocab).val() ?? '1';
        this.hideRecipientRegionField($(region_vocab), data.toString());
      });

      region_vocabulary.on('select2:select', (e) => {
        const data = e.params.data.id;
        const target = e.target as HTMLElement;

        this.hideRecipientRegionField($(target), data);
      });

      region_vocabulary.on('select2:clear', (e) => {
        const target = e.target as HTMLElement;

        this.hideRecipientRegionField($(target), '');
      });
    }
  }

  /**
   * Hides Recipient Region Form Fields
   */
  public hideRecipientRegionField(index: JQuery, value: string) {
    const case1_show = 'select[id*="[region_code]"]',
      case2_show = 'input[id*="[custom_code]"], input[id*="[code]"]',
      case99_show =
        'input[id*="[custom_code]"],input[id*="[vocabulary_uri]"], input[id*="[code]"]',
      case1 =
        'input[id*="[custom_code]"],input[id*="[vocabulary_uri]"],input[id*="[code]"]',
      case2 = 'select[id*="[region_code]"],input[id*="[vocabulary_uri]"]',
      case99 = 'select[id*="[region_code]"]';

    switch (value) {
      case '1':
        index
          .closest('.form-field-group')
          .find(case1_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case1)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '2':
        index
          .closest('.form-field-group')
          .find(case2_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case2)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '99':
        index
          .closest('.form-field-group')
          .find(case99_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case99)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      default:
        index
          .closest('.form-field-group')
          .find(case2_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case2)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
    }
  }

  /**
   * Updates Activity identifier
   */
  public updateActivityIdentifier() {
    const activity_identifier = $('#activity_identifier');

    if (activity_identifier.length > 0) {
      activity_identifier.on('keyup', function () {
        $('#iati_identifier_text').val(
          $('.identifier').attr('activity_identifier') + `-${$(this).val()}`
        );
      });

      activity_identifier.trigger('keyup');
    }
  }

  /**
   * Tag Form Page
   *
   * @Logic hide vocabulary-uri and codes field based on '@vocabulary' field value
   */
  public tagVocabularyHideField() {
    const tag_vocabulary = $('select[id*="tag_vocabulary"]');

    if (tag_vocabulary.length > 0) {
      $.each(tag_vocabulary, (index, tag) => {
        const data = $(tag).val() ?? '1';
        this.hideTagField($(tag), data.toString());
      });

      tag_vocabulary.on('select2:select', (e) => {
        const data = e.params.data.id;
        const target = e.target as HTMLElement;

        this.hideTagField($(target), data);
      });

      tag_vocabulary.on('select2:clear', (e) => {
        const target = e.target as HTMLElement;

        this.hideTagField($(target), '');
      });
    }
  }

  /**
   * Hide Tag Form fields
   */
  public hideTagField(index: JQuery, value: string) {
    const case1_show = 'input[id*="[tag_text]"]',
      case2_show = 'select[id*="[goals_tag_code]"]',
      case3_show = 'select[id*="[targets_tag_code]"]',
      case99_show = 'input[id*="[tag_text]"], input[id*="[vocabulary_uri]"]',
      case1 =
        'select[id*="[goals_tag_code]"],select[id*="[targets_tag_code]"],input[id*="[vocabulary_uri]"]',
      case2 =
        'input[id*="[vocabulary_uri]"],select[id*="[targets_tag_code]"],select[id*="[targets_tag_code]"],input[id*="[tag_text]"]',
      case3 =
        'input[id*="[vocabulary_uri]"],select[id*="[goals_tag_code]"],input[id*="[tag_text]"]',
      case99 =
        'select[id*="[goals_tag_code]"],select[id*="[targets_tag_code]"]';

    switch (value) {
      case '1':
        index
          .closest('.form-field-group')
          .find(case1_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case1)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '2':
        index
          .closest('.form-field-group')
          .find(case2_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case2)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '3':
        index
          .closest('.form-field-group')
          .find(case3_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case3)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      case '99':
        index
          .closest('.form-field-group')
          .find(case99_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case99)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
        break;
      default:
        index
          .closest('.form-field-group')
          .find(case1_show)
          .show()
          .removeAttr('disabled')
          .closest('.form-field')
          .show();
        index
          .closest('.form-field-group')
          .find(case1)
          .val('')
          .trigger('change')
          .hide()
          .attr('disabled', 'disabled')
          .closest('.form-field')
          .hide();
    }
  }
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
