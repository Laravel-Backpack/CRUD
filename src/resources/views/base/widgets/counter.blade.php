<div class="{{ $widget['wrapperClass'] ?? 'col-sm-6 col-md-3' }}">
    <div class="{{ $widget['class'] ?? 'card mb-2' }}">
        <div class="card-body p-3 d-flex align-items-center"><i class="la {{$widget['content']['icon']}} {{$widget['content']['icon_bg']}} p-3 font-5xl mr-3"></i>
            <div>
                <div class="text-value-sm text-primary">{{$widget['content']['number']}}</div>
                <div class="text-muted text-uppercase font-weight-bold small">{{$widget['content']['title']}}</div>
            </div>
        </div>
        <div class="card-footer px-3 py-2">
            <a class="btn-block text-muted d-flex justify-content-between align-items-center" href="{{$widget['content']['link']}}">
                <span class="small font-weight-bold">{{$widget['content']['link_title']}}</span>
                <i class="fa fa-angle-right"></i>
            </a>
        </div>
    </div>
</div>
