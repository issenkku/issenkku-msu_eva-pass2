<div data-background-library-region>
    @if(!empty($backgroundAssets))
        <div class="background-library" aria-label="background image library">
            <div class="background-library-title">รูปพื้นหลังที่มีในระบบ</div>
            <div class="background-library-grid">
                @foreach($backgroundAssets as $backgroundAsset)
                    <div
                        class="background-library-item {{ $selectedBackgroundPath === $backgroundAsset['path'] ? 'is-active' : '' }}"
                        data-background-path="{{ $backgroundAsset['path'] }}"
                        data-background-url="{{ $backgroundAsset['url'] }}"
                        role="button"
                        tabindex="0">
                        <button type="button" class="background-library-delete" aria-label="ลบรูปพื้นหลัง" title="ลบรูปนี้">
                            <i class="fas fa-times"></i>
                        </button>
                        <img src="{{ $backgroundAsset['url'] }}" alt="{{ $backgroundAsset['name'] }}">
                        <span>{{ $backgroundAsset['name'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
