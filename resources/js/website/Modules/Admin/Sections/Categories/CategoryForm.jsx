import { Link, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import ImageCropField from '../Banners/ImageCropField';

const emptyDefaults = {
    name: '',
    slug: '',
    description: '',
    parent_id: '',
    sort_order: 0,
    is_active: true,
};

export default function CategoryForm({
    options,
    category = null,
    submitUrl,
    method = 'post',
    submitLabel = 'Create Category',
    cancelUrl = '/admin/categories',
}) {
    const initial = useMemo(
        () => ({
            ...emptyDefaults,
            ...(category
                ? {
                      name: category.name ?? '',
                      slug: category.slug ?? '',
                      description: category.description ?? '',
                      parent_id: category.parent_id ?? '',
                      sort_order: category.sort_order ?? 0,
                      is_active: Boolean(category.is_active),
                  }
                : {}),
            image: null,
        }),
        [category],
    );

    const { data, setData, post, processing, errors, transform } = useForm(initial);
    const [slugTouched, setSlugTouched] = useState(Boolean(category?.slug));

    const onSubmit = (event) => {
        event.preventDefault();

        if (method === 'put') {
            transform((form) => ({
                ...form,
                _method: 'put',
                parent_id: form.parent_id === '' || form.parent_id === null ? null : form.parent_id,
            }));
        } else {
            transform((form) => ({
                ...form,
                parent_id: form.parent_id === '' || form.parent_id === null ? null : form.parent_id,
            }));
        }

        post(submitUrl, {
            forceFormData: true,
            preserveScroll: true,
        });
    };

    const onNameChange = (value) => {
        setData({
            ...data,
            name: value,
            slug: slugTouched ? data.slug : slugify(value),
        });
    };

    return (
        <form className="category-form" onSubmit={onSubmit}>
            <div className="category-form__grid">
                <label className="banner-form__field">
                    <span className="banner-form__label">Name</span>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.name}
                        onChange={(event) => onNameChange(event.target.value)}
                        required
                    />
                    {errors.name ? <span className="banner-form__error">{errors.name}</span> : null}
                </label>

                <label className="banner-form__field">
                    <span className="banner-form__label">Slug</span>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.slug}
                        onChange={(event) => {
                            setSlugTouched(true);
                            setData('slug', event.target.value);
                        }}
                        placeholder="auto-from-name"
                    />
                    {errors.slug ? <span className="banner-form__error">{errors.slug}</span> : null}
                </label>
            </div>

            <label className="banner-form__field">
                <span className="banner-form__label">Parent category</span>
                <select
                    className="banner-form__input"
                    value={data.parent_id ?? ''}
                    onChange={(event) => setData('parent_id', event.target.value)}
                >
                    {options.parents.map((item) => (
                        <option key={String(item.value)} value={item.value ?? ''}>
                            {item.label}
                        </option>
                    ))}
                </select>
                <span className="banner-form__hint">Leave as main category, or pick a main category for a sub category.</span>
                {errors.parent_id ? <span className="banner-form__error">{errors.parent_id}</span> : null}
            </label>

            <label className="banner-form__field">
                <span className="banner-form__label">Description</span>
                <input
                    className="banner-form__input"
                    type="text"
                    value={data.description}
                    onChange={(event) => setData('description', event.target.value)}
                />
                {errors.description ? (
                    <span className="banner-form__error">{errors.description}</span>
                ) : null}
            </label>

            <ImageCropField
                label="Category image"
                hint={options.imageHint ?? '1:1 crop · auto WebP under 200KB'}
                aspect={1}
                previewUrl={category?.image_url}
                error={errors.image}
                onChange={(file) => setData('image', file)}
            />

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
    );
}

function slugify(value) {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
