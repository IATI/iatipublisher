<div class="delete-confirmation fixed top-0 left-0 flex items-center justify-center w-screen h-screen p-8 modal z-[60]"
    style="display:none;">
    <div class="flex items-center justify-center w-full h-full">
        <div class="absolute top-0 left-0 w-full h-full opacity-50 modal-backdrop bg-n-50"></div>
        <div class="relative w-full max-h-full p-8 overflow-x-hidden bg-white rounded-lg modal-inner"
            style="max-width: 583px;">
            <div class="mb-4">
                <div class="flex mb-6 title">
                    <svg-vue icon="alert" class="mr-2 mt-0.5 text-lg text-crimson-40"></svg-vue>
                    <b>{{ trans('common/common.delete_alert') }}</b>
                </div>
                <div class="p-4 rounded-lg bg-rose">{{ trans('common/common.are_you_sure_you_want_to_delete_this_item') }}</div>
            </div>
            <div class="flex justify-end">
                <div class="inline-flex">
                    <button class="relative px-6 font-bold uppercase bg-white cancel-popup button text-n-40">
                        <span>{{trans('common/common.go_back')}}</span>
                    </button>
                    <button class="relative font-bold delete-confirm button text-n-40 primary-btn space">
                        <span>{{trans('common/common.delete')}}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="form-title ">
    <div class="flex flex-wrap  mb-4">
        <div class="flex  items-center  title grow">
            <span class="text-bluecoral  text-xl mr-1.5">
                @if (
                    $data['name'] === 'reporting_org' ||
                        $data['name'] === 'default_tied_status' ||
                        $data['name'] === 'crs_add' ||
                        $data['name'] === 'fss')
                    <svg-vue icon="activity-elements/building"></svg-vue>
                @else
                    <svg-vue icon="activity-elements/{{ $data['name'] }}"></svg-vue>
                @endif
            </span>
            <div class="text-sm font-bold title"> {{ trans(str_replace(' ', '-', strtolower($data['title']))) }}
            </div>
            @if (isCoreElement($data['name']))
                <svg-vue icon="core" class="ml-2"></svg-vue>
            @endif
            @if (isMandatoryElement($data['name']))
                <!-- <svg-vue icon="core" class="ml-2"></svg-vucorecoree>     -->
            @endif

            @if (Arr::get(getElementSchema($data['name']), 'show_info_in_title', false))
                @if (Arr::get(getElementSchema($data['name']), 'help_text', false))
                    <div>
                        <p
                            class="help-button text-xs text-n-40 hover:text-spring-50 font-normal ml-1.5 cursor-pointer inline-block">
                            Help</p>
                        <div class="help-button-content hidden">
                            <p class="font-bold text-bluecoral">
                                {{ $data['name'] }}
                            </p>
                            <div class="space-y-1.5">
                                {!! Arr::get(getElementSchema($data['name']), 'help_text', false) !!}
                            </div>
                        </div>
                    </div>
                @endif
                @if (Arr::get(getElementSchema($data['name']), 'hover_text', false))
                    <div class="help ml-1.5 font-normal"><svg fill="none" xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 15 14" class="text-sm">
                            <path
                                d="M7.385 4.667a1.1 1.1 0 0 0-.518.128c-.16.085-.295.209-.39.362l-.004.006-.003.007a.346.346 0 0 1-.093.105.438.438 0 0 1-.455.033.37.37 0 0 1-.113-.093.313.313 0 0 1-.06-.117.287.287 0 0 1-.005-.124l-.329-.058.329.058a.305.305 0 0 1 .049-.12l.004-.005.004-.006a1.75 1.75 0 0 1 .663-.615 1.92 1.92 0 0 1 2.092.176c.324.256.537.605.607.985.07.378-.005.77-.215 1.105-.211.337-.546.6-.952.737l-.227.076v.36a.31.31 0 0 1-.104.227.414.414 0 0 1-.28.106c-.11 0-.21-.04-.281-.106A.31.31 0 0 1 7 7.667V7a.31.31 0 0 1 .104-.227.414.414 0 0 1 .28-.106c.272 0 .536-.1.735-.285a.977.977 0 0 0 .317-.715.977.977 0 0 0-.317-.716 1.08 1.08 0 0 0-.734-.284Zm0 0V5v-.333Zm-.369 4.906.012-.021.009-.023a.16.16 0 0 1 .02-.037.736.736 0 0 1 .05-.06.391.391 0 0 1 .122-.074.433.433 0 0 1 .311 0l.12-.31-.12.31a.39.39 0 0 1 .127.079c.034.032.06.07.077.108a.3.3 0 0 1 .025.12v.015a.29.29 0 0 1-.02.118.263.263 0 0 1-.067.098l.227.244-.227-.244a.321.321 0 0 1-.116.068l-.01.003-.01.005a.386.386 0 0 1-.293 0l-.01-.005-.01-.003a.321.321 0 0 1-.117-.068l-.227.244.227-.244a.263.263 0 0 1-.067-.097l-.006-.015-.007-.015a.193.193 0 0 1-.022-.093V9.64a.51.51 0 0 1-.002-.057.078.078 0 0 1 .004-.01ZM3.572 1.74A7.245 7.245 0 0 1 7.385.667c.902 0 1.795.165 2.627.485.833.32 1.588.789 2.222 1.378a6.323 6.323 0 0 1 1.48 2.055c.341.766.517 1.587.517 2.415a6 6 0 0 1-1.147 3.51 6.76 6.76 0 0 1-3.072 2.338 7.362 7.362 0 0 1-3.968.363 7.033 7.033 0 0 1-3.51-1.741A6.201 6.201 0 0 1 .67 8.23a5.892 5.892 0 0 1 .387-3.645A6.467 6.467 0 0 1 3.572 1.74Zm.446 9.978a6.39 6.39 0 0 0 3.367.949c1.604 0 3.146-.592 4.288-1.652C12.815 9.955 13.46 8.511 13.46 7a5.4 5.4 0 0 0-1.03-3.158 6.007 6.007 0 0 0-2.729-2.08 6.488 6.488 0 0 0-3.498-.32 6.226 6.226 0 0 0-3.108 1.542 5.56 5.56 0 0 0-1.67 2.906 5.314 5.314 0 0 0 .348 3.287 5.773 5.773 0 0 0 2.244 2.54Z"
                                fill="#68797E" stroke="#68797E" stroke-width=".667"></path>
                        </svg>
                        <div class="left-0 help__text w-72">
                            <span class="absolute top-3 right-2 cursor-pointer close-help z-[50]"><svg
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 8 8" fill="none">
                                    <path fill="#2A2F30"
                                        d="M4.588 3.5 7.212.88a.418.418 0 0 0-.591-.592L4 2.913 1.38.288a.418.418 0 1 0-.593.591L3.413 3.5.787 6.12a.417.417 0 0 0 .136.684.417.417 0 0 0 .456-.091L4 4.088l2.62 2.625a.417.417 0 0 0 .684-.136.417.417 0 0 0-.092-.456L4.588 3.5Z" />
                                </svg></span>
                            <span class="font-bold text-bluecoral">{{ $data['name'] }}</span>
                            <p class="!text-black">{!! Arr::get(getElementSchema($data['name']), 'hover_text', false) !!} </p>
                        </div>
                    </div>
                @endif
            @endif
        </div>
        <div class="flex icons">
            <span class="text-xs"><span class="required-icon">*</span> {{ trans('common/common.mandatory_fields') }}</span>
        </div>
    </div>
    <div class="w-full h-px mb-4 divider bg-n-20"></div>
</div>
