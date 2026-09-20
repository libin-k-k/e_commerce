const positionLabels = {
    hero: 'Hero banner',
    middle: 'Middle banner',
    footer: 'Footer banner',
};

const styleLabels = {
    cinematic: 'Cinematic',
    dark_split: 'Dark split',
    gradient: 'Gradient',
};

export default function BannerLivePreview({
    title,
    subtitle,
    position,
    style,
    buttonText,
    navigationLink,
    webImageUrl,
    mobileImageUrl,
    isActive,
}) {
    const webSrc = webImageUrl || mobileImageUrl;
    const mobileSrc = mobileImageUrl || webImageUrl;
    const cta = buttonText?.trim() || (navigationLink === 'none' ? '' : 'Shop Now');
    const heading = title?.trim() || 'Banner title';
    const text = subtitle?.trim() || 'Subtitle appears here as you type.';

    return (
        <aside className="banner-live-preview" aria-live="polite">
            <div className="banner-live-preview__head">
                <h2 className="banner-live-preview__heading">Live preview</h2>
                <p className="banner-live-preview__meta">
                    {positionLabels[position] ?? position}
                    {' · '}
                    {styleLabels[style] ?? style}
                    {isActive ? '' : ' · Inactive'}
                </p>
            </div>

            <div className="banner-live-preview__stack">
                <div className="banner-live-preview__block">
                    <p className="banner-live-preview__label">Website</p>
                    <div className={`banner-live-preview__stage banner-live-preview__stage--web is-${style}`}>
                        {webSrc ? (
                            <img className="banner-live-preview__image" src={webSrc} alt="" />
                        ) : (
                            <div className="banner-live-preview__placeholder">Web image</div>
                        )}
                        <div className="banner-live-preview__overlay">
                            <div className="banner-live-preview__copy">
                                <p className="banner-live-preview__eyebrow">
                                    {positionLabels[position] ?? 'Banner'}
                                </p>
                                <h3 className="banner-live-preview__title">{heading}</h3>
                                <p className="banner-live-preview__text">{text}</p>
                                {cta ? <span className="banner-live-preview__cta">{cta}</span> : null}
                            </div>
                        </div>
                    </div>
                </div>

                <div className="banner-live-preview__block">
                    <p className="banner-live-preview__label">Mobile app (APK)</p>
                    <div className={`banner-live-preview__stage banner-live-preview__stage--mobile is-${style}`}>
                        {mobileSrc ? (
                            <img className="banner-live-preview__image" src={mobileSrc} alt="" />
                        ) : (
                            <div className="banner-live-preview__placeholder">Mobile image</div>
                        )}
                        <div className="banner-live-preview__overlay banner-live-preview__overlay--mobile">
                            <div className="banner-live-preview__copy">
                                <h3 className="banner-live-preview__title">{heading}</h3>
                                <p className="banner-live-preview__text">{text}</p>
                                {cta ? <span className="banner-live-preview__cta">{cta}</span> : null}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    );
}
