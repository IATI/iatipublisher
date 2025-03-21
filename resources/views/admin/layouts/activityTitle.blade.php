<div class="mb-4 xl:mb-6 page-title">
  <div class="flex items-end space-x-4">
    <div class="title grow-0">
      <div class="mb-4 activity__title text-caption-c1 text-n-40">
        <nav aria-label="breadcrumbs" class="rank-math-breadcrumb">
          <div class="flex">
            <a class="font-bold whitespace-nowrap" href="/activities">{{trans('common/common.your_activities')}}</a>
            <span class="mx-4 separator"> / </span>
            @php
                $count = count($data['bread_crumb_info']);
                $i = 0;
            @endphp
            @foreach($data['bread_crumb_info'] as $text => $link)
              <div class="breadcrumb__title">
                <span class="overflow-hidden breadcrumb__title text-n-30">
                   <a href="{{ $link }}">{{ $text }}</a>
                     @if(++$i < $count)
                       <span class="mx-4 separator"> / </span>
                     @endif
                </span>
                <span class="ellipsis__title--hover w-[calc(100%_+_37px)]">{{ $text }}</span>
              </div>
            @endforeach
            </div>
        </nav>
      </div>
      <div class="inline-flex items-center max-w-screen-md activity__title">
        <div class="mr-3">
          <a href="/activity/{{ $activity['id'] }}">
            <svg-vue icon="arrow-short-left"></svg-vue>
          </a>
        </div>
        <div>
          <h4 class="relative mr-4 text-2xl font-bold ellipsis__title">
            <span class="overflow-hidden ellipsis__title">{{ ucwords($data['form_header']) }}</span>
          </h4>
        </div>
      </div>
    </div>
  </div>
</div>
