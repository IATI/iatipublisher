<div class="page-title mb-4 xl:mb-6">
  <div class="flex items-end space-x-4">
    <div class="title grow-0">
      <div class="activity__title mb-4 text-caption-c1 text-n-40">
        <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
          <div class="flex items-center">
            <a class="whitespace-nowrap font-bold" href="/organisation">{{trans('organisationDetail/organisation_data.your_organisation')}}</a>
            <span class="separator mx-4"> / </span>
            <div class="breadcrumb__title">
              <span class="breadcrumb__title overflow-hidden last text-n-30">
                  <a href="/organisation">{{ $organization['name'][0]['narrative'] ?? getTranslatedUntitled() }}</a>
              </span>
              <span class="ellipsis__title--hover w-[calc(100%_+_35px)]">{{ $organization['name'][0]['narrative'] ?? getTranslatedUntitled() }}</span>
            </div>
            <span class="separator mx-4"> / </span>
            <div class="breadcrumb__title">
             <span class="breadcrumb__title overflow-hidden last text-n-30">
                <a href="{{sprintf("/organisation#%s",str_replace("-", "_", $data['name']))}}">
                    {{ucwords(str_replace("_", " ", str_replace("-", " ", $data['name'])))}}
                </a>
             </span>
             <span class="ellipsis__title--hover w-[calc(100%_+_35px)]">
                 {{ $organization['name'][0]['narrative'] ?? getTranslatedUntitled() }}
             </span>
            </div>
          </div>
        </nav>
      </div>
      <div class="activity__title inline-flex items-center max-w-screen-md">
        <div class="mr-3">
          <a href="/organisation">
            <svg-vue icon="arrow-short-left"></svg-vue>
          </a>
        </div>
        <div>
          <h4 class="ellipsis__title relative mr-4 text-2xl font-bold">
            <span
              class="ellipsis__title overflow-hidden">{{ $organization['name'][0]['narrative'] ?? getTranslatedUntitled() }}</span>
            <span class="ellipsis__title--hover">{{ $organization['name'][0]['narrative'] ?? getTranslatedUntitled() }}</span>
          </h4>
        </div>
      </div>
    </div>
  </div>
</div>
