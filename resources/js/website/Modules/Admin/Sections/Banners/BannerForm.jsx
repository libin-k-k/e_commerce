import { Link, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import BannerLivePreview from './BannerLivePreview';
import ImageCropField from './ImageCropField';

const emptyDefaults = {
    title: '',
    subtitle: '',
    position: 'hero',
    style: 'cinematic',
    navigation_link: 'none',
    custom_url: '',
    category_slug: '',
    button_text: '',
    sort_order: 0,
    is_active: true,
};

export default function BannerForm({
    options,
    banner = null,
    submitUrl,
    method = 'post',
    submitLabel = 'Create Banner',
    cancelUrl = '/admin/banners',
}) {
    const initial = useMemo(
        () => ({
            ...emptyDefaults,
            ...(banner
                ? {
                      title: banner.title ?? '',
                      subtitle: banner.subtitle ?? '',
                      position: banner.position ?? 'hero',
                      style: banner.style ?? 'cinematic',
                      navigation_link: banner.navigation_link ?? 'none',
                      custom_url: banner.custom_url ?? '',
                      category_slug: banner.category_slug ?? '',
                      button_text: banner.button_text ?? '',
                      sort_order: banner.sort_order ?? 0,
                      is_active: Boolean(banner.is_active),
                  }
                : {}),
            web_image: null,
            mobile_image: null,
        }),
        [banner],
    );

    const { data, setData, post, processing, errors, transform } = useForm(initial);
    const [webPreviewUrl, setWebPreviewUrl] = useState(banner?.web_image_url ?? null);
    const [mobilePreviewUrl, setMobilePreviewUrl] = useState(banner?.mobile_image_url ?? null);

    useEffect(() => {
        setWebPreviewUrl(banner?.web_image_url ?? null);
        setMobilePreviewUrl(banner?.mobile_image_url ?? null);
    }, [banner?.web_image_url, banner?.mobile_image_url]);

    useEffect(() => {
        return () => {
            revokeIfBlob(webPreviewUrl);
            revokeIfBlob(mobilePreviewUrl);
        };
        // Only revoke on unmount; intermediate replacements revoke in handlers.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const onSubmit = (event) => {
        event.preventDefault();

        if (method === 'put') {
            transform((form) => ({
                ...form,
                _method: 'put',
            }));
        }

        post(submitUrl, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const onWebImage = (file) => {
        setData('web_image', file);
        setWebPreviewUrl((current) => {
            revokeIfBlob(current);
            return file ? URL.createObjectURL(file) : (banner?.web_image_url ?? null);
        });
    };

    const onMobileImage = (file) => {
        setData('mobile_image', file);
        setMobilePreviewUrl((current) => {
            revokeIfBlob(current);
            return file ? URL.createObjectURL(file) : (banner?.mobile_image_url ?? null);
        });
    };

    const linkHint = data.navigation_link === 'none' ? '(no link set)' : '';

    return (
        <div className="banner-form-layout">
            <form className="banner-form" onSubmit={onSubmit}>
                <div className="banner-form__grid">
                    <label className="banner-form__field">
                        <span className="banner-form__label">Title</span>
                        <input
                            className="banner-form__input"
                            type="text"
                            value={data.title}
                            onChange={(event) => setData('title', event.target.value)}
                            required
                        />
                        {errors.title ? <span className="banner-form__error">{errors.title}</span> : null}
                    </label>

                    <label className="banner-form__field">
                        <span className="banner-form__label">Sub title</span>
                        <input
                            className="banner-form__input"
                            type="text"
                            value={data.subtitle}
                            onChange={(event) => setData('subtitle', event.target.value)}
                        />
                        {errors.subtitle ? (
                            <span className="banner-form__error">{errors.subtitle}</span>
                        ) : null}
                    </label>
                </div>

                <fieldset className="banner-form__fieldset">
                    <legend className="banner-form__legend">Banner Position</legend>
                    <div className="banner-form__chips">
                        {options.positions.map((item) => (
                            <button
                                key={item.value}
                                type="button"
                                className={`banner-form__chip${data.position === item.value ? ' is-active' : ''}`}
                                onClick={() => setData('position', item.value)}
                            >
                                {item.label}
                            </button>
                        ))}
                    </div>
                    {errors.position ? <span className="banner-form__error">{errors.position}</span> : null}
                </fieldset>

                <fieldset className="banner-form__fieldset">
                    <legend className="banner-form__legend">Banner Style</legend>
                    <div className="banner-form__chips">
                        {options.styles.map((item) => (
                            <button
                                key={item.value}
                                type="button"
                                className={`banner-form__chip${data.style === item.value ? ' is-active' : ''}`}
                                onClick={() => setData('style', item.value)}
                            >
                                {item.label}
                            </button>
                        ))}
                    </div>
                    {errors.style ? <span className="banner-form__error">{errors.style}</span> : null}
                </fieldset>

                <div className="banner-form__images">
                    <p className="banner-form__images-note">
                        Upload at least one image. Web banner is for the website. Mobile app banner is for the APK only.
                    </p>
                    <ImageCropField
                        label="Web banner"
                        hint={options.imageHints?.web ?? 'Website · 21:9 · desktop/tablet web'}
                        aspect={21 / 9}
                        previewUrl={banner?.web_image_url}
                        error={errors.web_image}
                        onChange={onWebImage}
                    />
                    <ImageCropField
                        label="Mobile app banner (APK)"
                        hint={options.imageHints?.mobile ?? 'Mobile app only · 4:5 · not shown on website'}
                        aspect={4 / 5}
                        previewUrl={banner?.mobile_image_url}
                        error={errors.mobile_image}
                        onChange={onMobileImage}
                    />
                </div>

                <fieldset className="banner-form__fieldset">
                    <legend className="banner-form__legend">Navigation Link</legend>
                    <div className="banner-nav-grid">
                        {options.navigationLinks.map((item) => (
                            <button
                                key={item.value}
                                type="button"
                                className={`banner-nav-tile${data.navigation_link === item.value ? ' is-active' : ''}`}
                                onClick={() => setData('navigation_link', item.value)}
                            >
                                <span className="banner-nav-tile__icon" aria-hidden="true">
                                    {navIcon(item.value)}
                                </span>
                                <span className="banner-nav-tile__label">{item.label}</span>
                            </button>
                        ))}
                    </div>
                    {errors.navigation_link ? (
                        <span className="banner-form__error">{errors.navigation_link}</span>
                    ) : null}
                </fieldset>

                {data.navigation_link === 'custom' ? (
                    <label className="banner-form__field">
                        <span className="banner-form__label">Custom URL</span>
                        <input
                            className="banner-form__input"
                            type="text"
                            value={data.custom_url}
                            onChange={(event) => setData('custom_url', event.target.value)}
                            placeholder="/products/example"
                        />
                        {errors.custom_url ? (
                            <span className="banner-form__error">{errors.custom_url}</span>
                        ) : null}
                    </label>
                ) : null}

                {data.navigation_link === 'category' ? (
                    <label className="banner-form__field">
                        <span className="banner-form__label">Category slug</span>
                        <input
                            className="banner-form__input"
                            type="text"
                            value={data.category_slug}
                            onChange={(event) => setData('category_slug', event.target.value)}
                            placeholder="western-wear"
                        />
                        {errors.category_slug ? (
                            <span className="banner-form__error">{errors.category_slug}</span>
                        ) : null}
                    </label>
                ) : null}

                <fieldset className="banner-form__fieldset">
                    <legend className="banner-form__legend">
                        Button Text <span className="banner-form__legend-hint">{linkHint}</span>
                    </legend>
                    <div className="banner-form__chips">
                        {options.buttonPresets.map((label) => (
                            <button
                                key={label}
                                type="button"
                                className={`banner-form__chip${data.button_text === label ? ' is-active' : ''}`}
                                onClick={() => setData('button_text', label)}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.button_text}
                        onChange={(event) => setData('button_text', event.target.value)}
                        placeholder="or type your own..."
                    />
                    {errors.button_text ? (
                        <span className="banner-form__error">{errors.button_text}</span>
                    ) : null}
                </fieldset>

                <div className="banner-form__grid banner-form__grid--compact">
                    <label className="banner-form__field">
                        <span className="banner-form__label">Sort Order</span>
                        <input
                            className="banner-form__input"
                            type="number"
                            min="0"
                            value={data.sort_order}
                            onChange={(event) => setData('sort_order', Number(event.target.value))}
                        />
                    </label>

                    <label className="banner-form__check">
                        <input
                            type="checkbox"
                            checked={data.is_active}
                            onChange={(event) => setData('is_active', event.target.checked)}
                        />
                        Active
                    </label>
                </div>

                <div className="banner-form__actions">
                    <button type="submit" className="btn btn--primary" disabled={processing}>
                        {processing ? 'Saving...' : submitLabel}
                    </button>
                    <Link href={cancelUrl} className="btn btn--ghost">
                        Cancel
                    </Link>
                </div>
            </form>

            <BannerLivePreview
                title={data.title}
                subtitle={data.subtitle}
                position={data.position}
                style={data.style}
                buttonText={data.button_text}
                navigationLink={data.navigation_link}
                webImageUrl={webPreviewUrl}
                mobileImageUrl={mobilePreviewUrl}
                isActive={data.is_active}
            />
        </div>
    );
}

function revokeIfBlob(url) {
    if (url && String(url).startsWith('blob:')) {
        URL.revokeObjectURL(url);
    }
}

function navIcon(value) {
    const map = {
        none: 'Ø',
        home: 'H',
        products: 'P',
        sale: '%',
        featured: '*',
        new_arrivals: 'N',
        category: '#',
        custom: '/',
    };

    return map[value] ?? '•';
}
