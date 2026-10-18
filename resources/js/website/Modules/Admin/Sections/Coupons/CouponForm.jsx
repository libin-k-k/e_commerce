import { Link, useForm } from '@inertiajs/react';
import { useMemo } from 'react';

const emptyDefaults = {
    code: '',
    name: '',
    type: 'percent',
    value: '',
    max_discount_amount: '',
    min_order_amount: '',
    max_order_amount: '',
    has_validity: false,
    starts_at: '',
    ends_at: '',
    total_usage_limit: '',
    per_user_usage_limit: '',
    first_order_only: false,
    is_active: true,
    product_ids: [],
};

export default function CouponForm({
    options,
    coupon = null,
    submitUrl,
    method = 'post',
    submitLabel = 'Create Coupon',
    cancelUrl = '/admin/coupons',
}) {
    const initial = useMemo(
        () => ({
            ...emptyDefaults,
            ...(coupon
                ? {
                      code: coupon.code ?? '',
                      name: coupon.name ?? '',
                      type: coupon.type ?? 'percent',
                      value: coupon.value ?? '',
                      max_discount_amount: coupon.max_discount_amount ?? '',
                      min_order_amount: coupon.min_order_amount ?? '',
                      max_order_amount: coupon.max_order_amount ?? '',
                      has_validity: Boolean(coupon.has_validity),
                      starts_at: coupon.starts_at ?? '',
                      ends_at: coupon.ends_at ?? '',
                      total_usage_limit: coupon.total_usage_limit ?? '',
                      per_user_usage_limit: coupon.per_user_usage_limit ?? '',
                      first_order_only: Boolean(coupon.first_order_only),
                      is_active: Boolean(coupon.is_active),
                      product_ids: coupon.product_ids ?? [],
                  }
                : {}),
        }),
        [coupon],
    );

    const { data, setData, post, processing, errors, transform } = useForm(initial);

    const onSubmit = (event) => {
        event.preventDefault();

        transform((form) => ({
            ...form,
            ...(method === 'put' ? { _method: 'put' } : {}),
            max_discount_amount: form.max_discount_amount === '' ? null : form.max_discount_amount,
            min_order_amount: form.min_order_amount === '' ? null : form.min_order_amount,
            max_order_amount: form.max_order_amount === '' ? null : form.max_order_amount,
            total_usage_limit: form.total_usage_limit === '' ? null : form.total_usage_limit,
            per_user_usage_limit: form.per_user_usage_limit === '' ? null : form.per_user_usage_limit,
            starts_at: form.has_validity ? form.starts_at || null : null,
            ends_at: form.has_validity ? form.ends_at || null : null,
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
        setData('product_ids', [...current, id]);
    };

    return (
        <form className="product-form" onSubmit={onSubmit}>
            <div className="product-form__grid">
                <label className="banner-form__field">
                    <span className="banner-form__label">Coupon code</span>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.code}
                        onChange={(event) => setData('code', event.target.value.toUpperCase())}
                        required
                    />
                    {errors.code ? <span className="banner-form__error">{errors.code}</span> : null}
                </label>
                <label className="banner-form__field">
                    <span className="banner-form__label">Display name (optional)</span>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.name}
                        onChange={(event) => setData('name', event.target.value)}
                    />
                </label>
            </div>

            <div className="product-form__grid product-form__grid--3">
                <label className="banner-form__field">
                    <span className="banner-form__label">Type</span>
                    <select
                        className="banner-form__input"
                        value={data.type}
                        onChange={(event) => setData('type', event.target.value)}
                    >
                        {(options.types ?? []).map((type) => (
                            <option key={type.value} value={type.value}>
                                {type.label}
                            </option>
                        ))}
                    </select>
                </label>
                <label className="banner-form__field">
                    <span className="banner-form__label">
                        {data.type === 'percent' ? 'Percentage (%)' : 'Fixed amount (₹)'}
                    </span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0.01"
                        step="0.01"
                        value={data.value}
                        onChange={(event) => setData('value', event.target.value)}
                        required
                    />
                    {errors.value ? <span className="banner-form__error">{errors.value}</span> : null}
                </label>
                <label className="banner-form__field">
                    <span className="banner-form__label">
                        Max discount amount (₹){data.type === 'percent' ? ' *' : ''}
                    </span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0.01"
                        step="0.01"
                        value={data.max_discount_amount}
                        onChange={(event) => setData('max_discount_amount', event.target.value)}
                        required={data.type === 'percent'}
                        placeholder="e.g. 100"
                    />
                    <span className="banner-form__hint">
                        For 20% off with max ₹100 discount, set value 20 and max 100.
                    </span>
                    {errors.max_discount_amount ? (
                        <span className="banner-form__error">{errors.max_discount_amount}</span>
                    ) : null}
                </label>
            </div>

            <div className="product-form__grid">
                <label className="banner-form__field">
                    <span className="banner-form__label">Min order amount (₹)</span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.min_order_amount}
                        onChange={(event) => setData('min_order_amount', event.target.value)}
                    />
                </label>
                <label className="banner-form__field">
                    <span className="banner-form__label">Max order amount (₹)</span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.max_order_amount}
                        onChange={(event) => setData('max_order_amount', event.target.value)}
                    />
                </label>
            </div>

            <fieldset className="banner-form__fieldset">
                <legend className="banner-form__legend">Validity</legend>
                <label className="banner-form__check">
                    <input
                        type="checkbox"
                        checked={data.has_validity}
                        onChange={(event) => setData('has_validity', event.target.checked)}
                    />
                    Limit by from / to date
                </label>
                {!data.has_validity ? (
                    <p className="product-form__hint">No validity dates. Coupon can be used anytime while active.</p>
                ) : (
                    <div className="product-form__grid">
                        <label className="banner-form__field">
                            <span className="banner-form__label">From date</span>
                            <input
                                className="banner-form__input"
                                type="datetime-local"
                                value={data.starts_at}
                                onChange={(event) => setData('starts_at', event.target.value)}
                            />
                            {errors.starts_at ? (
                                <span className="banner-form__error">{errors.starts_at}</span>
                            ) : null}
                        </label>
                        <label className="banner-form__field">
                            <span className="banner-form__label">To date</span>
                            <input
                                className="banner-form__input"
                                type="datetime-local"
                                value={data.ends_at}
                                onChange={(event) => setData('ends_at', event.target.value)}
                            />
                            {errors.ends_at ? <span className="banner-form__error">{errors.ends_at}</span> : null}
                        </label>
                    </div>
                )}
            </fieldset>

            <div className="product-form__grid">
                <label className="banner-form__field">
                    <span className="banner-form__label">Total apply count</span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="1"
                        value={data.total_usage_limit}
                        onChange={(event) => setData('total_usage_limit', event.target.value)}
                        placeholder="e.g. 5 users total"
                    />
                    <span className="banner-form__hint">Leave empty for unlimited total uses.</span>
                </label>
                <label className="banner-form__field">
                    <span className="banner-form__label">Per-user apply count</span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="1"
                        value={data.per_user_usage_limit}
                        onChange={(event) => setData('per_user_usage_limit', event.target.value)}
                        placeholder="e.g. 2 times per user"
                    />
                    <span className="banner-form__hint">Leave empty for unlimited uses per user.</span>
                </label>
            </div>

            <fieldset className="banner-form__fieldset">
                <legend className="banner-form__legend">Products (optional)</legend>
                <p className="product-form__hint">
                    Leave all unchecked to apply on any product. Select products to restrict the coupon.
                </p>
                <div className="coupon-product-list">
                    {(options.products ?? []).map((product) => (
                        <label key={product.value} className="banner-form__check">
                            <input
                                type="checkbox"
                                checked={data.product_ids.map(Number).includes(Number(product.value))}
                                onChange={() => toggleProduct(product.value)}
                            />
                            {product.label}
                        </label>
                    ))}
                </div>
                {errors.product_ids ? (
                    <span className="banner-form__error">{errors.product_ids}</span>
                ) : null}
            </fieldset>

            <div className="product-form__grid banner-form__grid--compact">
                <label className="banner-form__check product-form__switch">
                    <input
                        type="checkbox"
                        checked={data.first_order_only}
                        onChange={(event) => setData('first_order_only', event.target.checked)}
                    />
                    First order coupon only
                </label>
                <label className="banner-form__check product-form__switch">
                    <input
                        type="checkbox"
                        checked={data.is_active}
                        onChange={(event) => setData('is_active', event.target.checked)}
                    />
                    Active
                </label>
            </div>

            <div className="banner-form__actions product-form__actions">
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
