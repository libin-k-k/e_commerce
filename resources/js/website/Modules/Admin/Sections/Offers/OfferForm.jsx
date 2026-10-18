import { Link, useForm } from '@inertiajs/react';
import { useMemo } from 'react';

const emptyDefaults = {
    title: '',
    starts_at: '',
    ends_at: '',
    countdown_enabled: false,
    is_active: true,
    sort_order: 0,
    product_ids: [],
};

export default function OfferForm({
    options,
    offer = null,
    submitUrl,
    method = 'post',
    submitLabel = 'Save offer',
    cancelUrl = '/admin/offers',
}) {
    const limit = options.productLimit ?? 4;
    const initial = useMemo(
        () => ({
            ...emptyDefaults,
            ...(offer
                ? {
                      title: offer.title ?? '',
                      starts_at: offer.starts_at ?? '',
                      ends_at: offer.ends_at ?? '',
                      countdown_enabled: Boolean(offer.countdown_enabled),
                      is_active: Boolean(offer.is_active),
                      sort_order: offer.sort_order ?? 0,
                      product_ids: offer.product_ids ?? [],
                  }
                : {}),
        }),
        [offer],
    );

    const { data, setData, post, processing, errors, transform } = useForm(initial);

    const onSubmit = (event) => {
        event.preventDefault();
        transform((form) => ({
            ...form,
            ...(method === 'put' ? { _method: 'put' } : {}),
            starts_at: form.starts_at || null,
            ends_at: form.ends_at || null,
        }));
        post(submitUrl, { preserveScroll: true });
    };

    const toggleProduct = (productId) => {
        const id = Number(productId);
        const current = data.product_ids.map(Number);

        if (current.includes(id)) {
            setData(
                'product_ids',
                current.filter((item) => item !== id),
            );

            return;
        }

        if (current.length >= limit) {
            return;
        }

        setData('product_ids', [...current, id]);
    };

    return (
        <form className="product-form" onSubmit={onSubmit}>
            <label className="banner-form__field">
                <span className="banner-form__label">Card title</span>
                <input
                    className="banner-form__input"
                    type="text"
                    value={data.title}
                    onChange={(event) => setData('title', event.target.value)}
                    required
                />
                {errors.title ? <span className="banner-form__error">{errors.title}</span> : null}
            </label>

            <div className="product-form__grid">
                <label className="banner-form__field">
                    <span className="banner-form__label">Starts</span>
                    <input
                        className="banner-form__input"
                        type="datetime-local"
                        value={data.starts_at}
                        onChange={(event) => setData('starts_at', event.target.value)}
                    />
                    {errors.starts_at ? <span className="banner-form__error">{errors.starts_at}</span> : null}
                </label>
                <label className="banner-form__field">
                    <span className="banner-form__label">Ends</span>
                    <input
                        className="banner-form__input"
                        type="datetime-local"
                        value={data.ends_at}
                        onChange={(event) => setData('ends_at', event.target.value)}
                    />
                    {errors.ends_at ? <span className="banner-form__error">{errors.ends_at}</span> : null}
                </label>
            </div>

            <div className="banner-form__grid banner-form__grid--compact">
                <label className="banner-form__field">
                    <span className="banner-form__label">Sort order</span>
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
                        checked={data.countdown_enabled}
                        onChange={(event) => setData('countdown_enabled', event.target.checked)}
                    />
                    Countdown
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

            <fieldset className="banner-form__fieldset">
                <legend className="banner-form__legend">Products ({data.product_ids.length}/{limit})</legend>
                <div className="banner-sections__list">
                    {(options.products ?? []).map((product) => (
                        <label key={product.id} className="banner-form__check">
                            <input
                                type="checkbox"
                                checked={data.product_ids.map(Number).includes(Number(product.id))}
                                onChange={() => toggleProduct(product.id)}
                            />
                            {product.name}
                        </label>
                    ))}
                </div>
                {errors.product_ids ? <span className="banner-form__error">{errors.product_ids}</span> : null}
            </fieldset>

            <div className="banner-form__actions">
                <button type="submit" className="btn btn--primary" disabled={processing}>
                    {processing ? 'Saving...' : submitLabel}
                </button>
                <Link href={cancelUrl} className="btn btn--ghost">
                    Cancel
                </Link>
            </div>
        </form>
    );
}
