@extends('admin.layouts.app')

@section("content")

    <section class="section">
        <div class="title-form-layout">
            <div class="pt-4 pb-6  max-w-[1000px] mx-auto">

                {{-- page title --}}
                <div class="page-title mb-6">
                    <div class="flex items-end gap-4">
                        <div class="title grow-0">
                            <div class="mb-4 text-caption-c1 text-n-40">
                                <nav aria-label="breadcrumbs" class="breadcrumb">
                                    <p>
                                        <a href="/activities" class="font-bold">Your Activities</a>
                                        <span class="separator mx-4"> / </span>
                                        <span class="last text-n-30"
                                        >Partnership against child exploitation</span
                                        >
                                    </p>
                                </nav>
                            </div>
                            <div class="inline-flex items-center">
                                <div class="mr-3">
                                    <a href="/activity/1">
                                        <svg-vue icon="arrow-short-left"></svg-vue>
                                    </a>
                                </div>
                                <h4 class="mr-4 font-bold">
                                    Partnership Against Child Exploitation
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- form section --}}
                <form action="" method="post">
                    <div class="title-form-layout__container p-8 bg-white rounded-lg overflow-y-auto h-[62vh]">
                        {{-- title --}}
                        <div class="flex mb-4">
                            <div class="flex title grow">
                                <span class="text-bluecoral text-xl mr-1.5">
                                @php
                                    echo file_get_contents(resource_path('assets/images/svg/note.svg'))
                                @endphp
                                </span>
                                <div class="title text-sm font-bold">Activity Title</div>
                                <div class="flex status text-xs leading-5 text-crimson-50 ml-2.5 mr-2.5">
                                    <b class="mr-2 text-base leading-3">.</b>
                                    <span>not completed</span>
                                </div>
                                @php
                                    echo file_get_contents(resource_path('assets/images/svg/core.svg'))
                                @endphp
                            </div>
                            <div class="icons flex">
                                <span class="text-xs"><sup class="text-salmon-50">*</sup> {{ trans('common/common.mandatory_fields') }}</span>
                                <hover-text hoverText="tooltip" class="ml-1"/>
                            </div>
                        </div>
                        {{-- divider --}}
                        <div class="divider h-px bg-n-20 w-full mb-4"></div>
                        {{-- Form Repeaters --}}
                        <div class="form-repeaters">
                            <div class="form-field-group">
                                <div class="-mx-3 flex flex-wrap">
                                    <div class="form-field basis-6/12">
                                        <div class="form-field-input">
                                            <div class="form-field-label">
                                                <label for="">
                                                    Narrative
                                                    <sup class="required">*</sup>
                                                </label>
                                                <hover-text hoverText="tooltip"/>
                                            </div>
                                            <input type="text" placeholder="Enter an activity title text" value="">
                                            <div class="help-text">
                                                This is a help text
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-field basis-6/12 px-3">
                                        <div class="form-field-select">
                                            <div class="form-field-label flex justify-between">
                                                <label for="">
                                                    @xml: lang
                                                    <sup class="required">*</sup>
                                                </label>
                                                <hover-text hoverText="tooltip"/>
                                            </div>
                                            <select>
                                                <option>Select a language</option>
                                                <option value="en">en - English</option>
                                                <option value="fr">fr - France</option>
                                                <option value="es">es - Spanish</option>
                                            </select>
                                            <div class="help-text text-xs leading-relaxed mt-2 text-n-40">
                                                This is a help text
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="delete-item">
                                    @php
                                        echo file_get_contents(resource_path('assets/images/svg/delete.svg'))
                                    @endphp
                                </span>
                            </div>
                            <div class="form-field-group">
                                <div class="-mx-3 flex flex-wrap">
                                    <div class="form-field basis-6/12">
                                        <div class="form-field-input">
                                            <div class="form-field-label">
                                                <label for="">
                                                    Narrative
                                                    <sup class="required">*</sup>
                                                </label>
                                                <hover-text hoverText="tooltip"/>
                                            </div>
                                            <input type="text" placeholder="Enter an activity title text" value="">
                                            <div class="help-text">
                                                This is a help text
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-field basis-6/12 px-3">
                                        <div class="form-field-select">
                                            <div class="form-field-label flex justify-between">
                                                <label for="">
                                                    @xml: lang
                                                    <sup class="required">*</sup>
                                                </label>
                                                <hover-text hoverText="tooltip"/>
                                            </div>
                                            <select>
                                                <option>Select a language</option>
                                                <option value="en">en - English</option>
                                                <option value="fr">fr - France</option>
                                                <option value="es">es - Spanish</option>
                                            </select>
                                            <div class="help-text text-xs leading-relaxed mt-2 text-n-40">
                                                This is a help text
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <span class="delete-item">
                                    @php
                                        echo file_get_contents(resource_path('assets/images/svg/delete.svg'))
                                    @endphp
                                </span>
                            </div>
                        </div>
                        {{-- buttons --}}
                        <div class="fixed left-0 bottom-0 w-full bg-eggshell py-5 pr-40 shadow-dropdown">
                            <div class="flex items-center justify-end"><a class="ghost-btn mr-8" href="/activities">Cancel</a>
                                <button class="primary-btn save-btn">Save publishing setting</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection
<script defer>
    import HoverText from '../../../assets/js/components/HoverText';

    export default {
        components: { HoverText }
    };
</script>
