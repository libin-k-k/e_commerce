import { useForm } from '@inertiajs/react';
import { useMemo } from 'react';

export default function OfferPageForm({ pageSetting, themes = [] }) {
    const initial = useMemo(
        () => ({
            section_title: pageSetting?.section_title ?? 'Offer Zone',
            section_subtitle: pageSetting?.section_subtitle ?? '',
            eyebrow: pageSetting?.eyebrow ?? 'Offer Zone',
            theme: pageSetting?.theme ?? 'classic',
            countdown_enabled: Boolean(pageSetting?.countdown_enabled),
            countdown_ends_at: pageSetting?.countdown_ends_at ?? '',
            is_enabled: pageSetting?.is_enabled !== false,
        }),
        [pageSetting],
    );

    const { data, setData, post, processing, errors, transform } = useForm(initial);

    const onSubmit = (event) => {
        event.preventDefault();
        transform((form) => ({
            ...form,
            _method: 'put',
            section_subtitle: form.section_subtitle || null,
            countdown_ends_at: form.countdown_ends_at || null,
        }));
        post('/admin/offers/settings', { preserveScroll: true });
    };

    return (
        <form className="product-form offer-page-form" onSubmit={onSubmit}>
            <div className="product-form__grid">
                <label className="banner-form__field">
                    <span className="banner-form__label">Section title</span>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.section_title}
                        onChange={(event) => setData('section_title', event.target.value)}
                        required
                    />
                    {errors.section_title ? <span className="banner-form__error">{errors.section_title}</span> : null}
                </label>
                <label className="banner-form__field">
                    <span className="banner-form__label">Eyebrow</span>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.eyebrow}
                        onChange={(event) => setData('eyebrow', event.target.value)}
                        required
                    />
                    {errors.eyebrow ? <span className="banner-form__error">{errors.eyebrow}</span> : null}
                </label>
            </div>

            <label className="banner-form__field">
                <span className="banner-form__label">Subtitle</span>
                <input
                    className="banner-form__input"
                    type="text"
                    value={data.section_subtitle}
                    onChange={(event) => setData('section_subtitle', event.target.value)}
                />
                {errors.section_subtitle ? <span className="banner-form__error">{errors.section_subtitle}</span> : null}
            </label>

            <label className="banner-form__field">
                <span className="banner-form__label">Season theme</span>
                <select
                    className="banner-form__input"
                    value={data.theme}
                    onChange={(event) => setData('theme', event.target.value)}
                >
                    {themes.map((theme) => (
                        <option key={theme.value} value={theme.value}>
                            {theme.label}
                        </option>
                    ))}
                </select>
                {errors.theme ? <span className="banner-form__error">{errors.theme}</span> : null}
            </label>

            <div className="banner-form__grid banner-form__grid--compact">
                <label className="banner-form__field">
                    <span className="banner-form__label">Countdown ends</span>
                    <input
                        className="banner-form__input"
                        type="datetime-local"
                        value={data.countdown_ends_at}
                        onChange={(event) => setData('countdown_ends_at', event.target.value)}
                    />
                    {errors.countdown_ends_at ? (
                        <span className="banner-form__error">{errors.countdown_ends_at}</span>
                    ) : null}
                </label>
                <label className="banner-form__check">
                    <input
                        type="checkbox"
                        checked={data.countdown_enabled}
                        onChange={(event) => setData('countdown_enabled', event.target.checked)}
                    />
                    Countdown on the offer banner
                </label>
                <label className="banner-form__check">
                    <input
                        type="checkbox"
                        checked={data.is_enabled}
                        onChange={(event) => setData('is_enabled', event.target.checked)}
                    />
                    Show Offer Zone on the home page
                </label>
            </div>

            <div className="banner-form__actions">
                <button type="submit" className="btn btn--primary" disabled={processing}>
                    {processing ? 'Saving...' : 'Save offer page'}
                </button>
            </div>
        </form>
    );
}
