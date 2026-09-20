import { Link, useForm } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import ImageCropField from '../Banners/ImageCropField';
import ProductPreview from './ProductPreview';
import RichTextEditor from './RichTextEditor';

const emptyDefaults = {
    name: '',
    slug: '',
    sku: '',
    short_description: '',
    description: '',
    price: '',
    sale_price: '',
    stock: 0,
    rating: 0,
    category_id: '',
    subcategory_id: '',
    sort_order: 0,
    is_unlaunched: false,
};

export default function ProductForm({
    options,
    product = null,
    submitUrl,
    method = 'post',
    submitLabel = 'Create Product',
    cancelUrl = '/admin/products',
}) {
    const initial = useMemo(
        () => ({
            ...emptyDefaults,
            ...(product
                ? {
                      name: product.name ?? '',
                      slug: product.slug ?? '',
                      sku: product.sku ?? '',
                      short_description: product.short_description ?? '',
                      description: product.description ?? '',
                      price: product.price ?? '',
                      sale_price: product.sale_price ?? '',
                      stock: product.stock ?? 0,
                      rating: product.rating ?? 0,
                      category_id: product.category_id ?? '',
                      subcategory_id: product.subcategory_id ?? '',
                      sort_order: product.sort_order ?? 0,
                      is_unlaunched: Boolean(product.is_unlaunched),
                  }
                : {}),
            main_image: null,
            additional_images: [],
            remove_image_ids: [],
            sizes: JSON.stringify((product?.sizes ?? []).map((item) => item.name ?? item)),
            colors: JSON.stringify(
                (product?.colors ?? []).map((item) => ({
                    name: item.name ?? '',
                    hex: item.hex ?? '#000000',
                })),
            ),
            variants: JSON.stringify(product?.variants ?? []),
        }),
        [product],
    );

    const { data, setData, post, processing, errors, transform } = useForm(initial);
    const [slugTouched, setSlugTouched] = useState(Boolean(product?.slug));
    const [sizes, setSizes] = useState(() => (product?.sizes ?? []).map((item) => item.name ?? item));
    const [colors, setColors] = useState(() =>
        (product?.colors ?? []).map((item) => ({
            name: item.name ?? '',
            hex: item.hex ?? '#000000',
        })),
    );
    const [variants, setVariants] = useState(() =>
        (product?.variants ?? []).map((row) => ({
            size: row.size ?? '',
            color: row.color ?? '',
            sku: row.sku ?? '',
            price: row.price ?? product?.price ?? '',
            sale_price: row.sale_price ?? '',
            stock: row.stock ?? 0,
        })),
    );
    const [existingImages, setExistingImages] = useState(product?.images ?? []);
    const [removeImageIds, setRemoveImageIds] = useState([]);
    const [previewOpen, setPreviewOpen] = useState(false);
    const [mainPreviewUrl, setMainPreviewUrl] = useState(product?.main_image_url ?? null);
    const [sizeDraft, setSizeDraft] = useState('');
    const [colorDraft, setColorDraft] = useState({ name: '', hex: '#111827' });

    const selectedCategory = options.categories.find(
        (item) => String(item.value) === String(data.category_id),
    );
    const subOptions = selectedCategory?.children ?? [];

    const cleanSizes = sizes.map((item) => String(item).trim()).filter(Boolean);
    const cleanColors = colors
        .map((item) => ({
            name: String(item.name ?? '').trim(),
            hex: item.hex || '#000000',
        }))
        .filter((item) => item.name !== '');

    useEffect(() => {
        const nextCombos = buildCombinations(cleanSizes, cleanColors.map((item) => item.name));
        setVariants((current) =>
            nextCombos.map((combo) => {
                const existing = current.find(
                    (row) =>
                        String(row.size ?? '') === String(combo.size ?? '') &&
                        String(row.color ?? '') === String(combo.color ?? ''),
                );

                if (existing) {
                    return existing;
                }

                const suffix = [combo.size, combo.color]
                    .filter(Boolean)
                    .map((part) =>
                        String(part)
                            .toUpperCase()
                            .replace(/[^A-Z0-9]+/g, ''),
                    )
                    .join('-');

                return {
                    size: combo.size ?? '',
                    color: combo.color ?? '',
                    sku: data.sku && suffix ? `${String(data.sku).toUpperCase()}-${suffix}` : '',
                    price: data.price || '',
                    sale_price: data.sale_price || '',
                    stock: data.stock || 0,
                };
            }),
        );
        // Rebuild matrix when option axes change; base price/sku only seed new rows.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [cleanSizes.join('|'), cleanColors.map((item) => item.name).join('|')]);

    const variantStockTotal = variants.reduce((sum, row) => sum + Number(row.stock || 0), 0);
    const hasVariantMatrix = cleanSizes.length > 0 || cleanColors.length > 0;

    const onSubmit = (event) => {
        event.preventDefault();

        transform((form) => ({
            ...form,
            ...(method === 'put' ? { _method: 'put' } : {}),
            sizes: JSON.stringify(cleanSizes),
            colors: JSON.stringify(cleanColors),
            variants: JSON.stringify(
                variants.map((row) => ({
                    size: row.size || null,
                    color: row.color || null,
                    sku: row.sku || null,
                    price: row.price,
                    sale_price: row.sale_price === '' ? null : row.sale_price,
                    stock: Number(row.stock || 0),
                })),
            ),
            remove_image_ids: JSON.stringify(removeImageIds),
            subcategory_id:
                form.subcategory_id === '' || form.subcategory_id === null
                    ? null
                    : form.subcategory_id,
            sale_price: form.sale_price === '' ? null : form.sale_price,
            stock: hasVariantMatrix ? variantStockTotal : form.stock,
        }));

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

    const onCategoryChange = (value) => {
        setData({
            ...data,
            category_id: value,
            subcategory_id: '',
        });
    };

    const updateVariant = (index, field, value) => {
        setVariants((current) => {
            const next = [...current];
            next[index] = { ...next[index], [field]: value };
            return next;
        });
    };

    const removeExistingImage = (id) => {
        setRemoveImageIds((current) => [...current, id]);
        setExistingImages((current) => current.filter((image) => image.id !== id));
    };

    const addSize = () => {
        const next = sizeDraft.trim();
        if (next === '') {
            return;
        }
        if (sizes.some((item) => String(item).trim().toLowerCase() === next.toLowerCase())) {
            setSizeDraft('');
            return;
        }
        setSizes([...sizes, next]);
        setSizeDraft('');
    };

    const addColor = () => {
        const nextName = colorDraft.name.trim();
        if (nextName === '') {
            return;
        }
        if (colors.some((item) => String(item.name).trim().toLowerCase() === nextName.toLowerCase())) {
            setColorDraft({ name: '', hex: colorDraft.hex || '#111827' });
            return;
        }
        setColors([...colors, { name: nextName, hex: colorDraft.hex || '#111827' }]);
        setColorDraft({ name: '', hex: '#111827' });
    };

    return (
        <>
        <form className="product-form" onSubmit={onSubmit}>
            <div className="product-form__grid">
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
                    <span className="banner-form__label">Base SKU</span>
                    <input
                        className="banner-form__input"
                        type="text"
                        value={data.sku}
                        onChange={(event) => setData('sku', event.target.value)}
                        required
                    />
                    {errors.sku ? <span className="banner-form__error">{errors.sku}</span> : null}
                </label>
            </div>

            <div className="product-form__grid">
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
                    />
                    {errors.slug ? <span className="banner-form__error">{errors.slug}</span> : null}
                </label>

                <label className="banner-form__field">
                    <span className="banner-form__label">Rating (0-5)</span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0"
                        max="5"
                        step="0.1"
                        value={data.rating}
                        onChange={(event) => setData('rating', event.target.value)}
                    />
                    {errors.rating ? <span className="banner-form__error">{errors.rating}</span> : null}
                </label>
            </div>

            <label className="banner-form__field">
                <span className="banner-form__label">Short description</span>
                <input
                    className="banner-form__input"
                    type="text"
                    value={data.short_description}
                    onChange={(event) => setData('short_description', event.target.value)}
                />
                {errors.short_description ? (
                    <span className="banner-form__error">{errors.short_description}</span>
                ) : null}
            </label>

            <div className="banner-form__field">
                <span className="banner-form__label">Description</span>
                <RichTextEditor
                    value={data.description}
                    onChange={(html) => setData('description', html)}
                    error={errors.description}
                />
            </div>

            <div className="product-form__grid product-form__grid--3">
                <label className="banner-form__field">
                    <span className="banner-form__label">
                        {hasVariantMatrix ? 'Base price (default)' : 'Price'}
                    </span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.price}
                        onChange={(event) => setData('price', event.target.value)}
                        required
                    />
                    {errors.price ? <span className="banner-form__error">{errors.price}</span> : null}
                </label>

                <label className="banner-form__field">
                    <span className="banner-form__label">
                        {hasVariantMatrix ? 'Base sale price' : 'Sale price'}
                    </span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0"
                        step="0.01"
                        value={data.sale_price}
                        onChange={(event) => setData('sale_price', event.target.value)}
                    />
                    {errors.sale_price ? (
                        <span className="banner-form__error">{errors.sale_price}</span>
                    ) : null}
                </label>

                <label className="banner-form__field">
                    <span className="banner-form__label">
                        {hasVariantMatrix ? 'Total stock (from variants)' : 'Stock'}
                    </span>
                    <input
                        className="banner-form__input"
                        type="number"
                        min="0"
                        value={hasVariantMatrix ? variantStockTotal : data.stock}
                        onChange={(event) => setData('stock', Number(event.target.value))}
                        disabled={hasVariantMatrix}
                        required={!hasVariantMatrix}
                    />
                    {errors.stock ? <span className="banner-form__error">{errors.stock}</span> : null}
                </label>
            </div>

            <div className="product-form__grid">
                <label className="banner-form__field">
                    <span className="banner-form__label">Category</span>
                    <select
                        className="banner-form__input"
                        value={data.category_id}
                        onChange={(event) => onCategoryChange(event.target.value)}
                        required
                    >
                        <option value="">Select main category</option>
                        {options.categories.map((item) => (
                            <option key={item.value} value={item.value}>
                                {item.label}
                            </option>
                        ))}
                    </select>
                    {errors.category_id ? (
                        <span className="banner-form__error">{errors.category_id}</span>
                    ) : null}
                </label>

                <label className="banner-form__field">
                    <span className="banner-form__label">Sub category</span>
                    <select
                        className="banner-form__input"
                        value={data.subcategory_id ?? ''}
                        onChange={(event) => setData('subcategory_id', event.target.value)}
                        disabled={subOptions.length === 0}
                    >
                        <option value="">None</option>
                        {subOptions.map((item) => (
                            <option key={item.value} value={item.value}>
                                {item.label}
                            </option>
                        ))}
                    </select>
                    {errors.subcategory_id ? (
                        <span className="banner-form__error">{errors.subcategory_id}</span>
                    ) : null}
                </label>
            </div>

            <ImageCropField
                label="Main image"
                hint={options.imageHint ?? '1:1 crop · auto WebP under 200KB'}
                aspect={1}
                previewUrl={product?.main_image_url}
                error={errors.main_image}
                onChange={(file) => {
                    setData('main_image', file);
                    if (file) {
                        setMainPreviewUrl(URL.createObjectURL(file));
                    }
                }}
            />

            <fieldset className="banner-form__fieldset">
                <legend className="banner-form__legend">Additional images</legend>
                {existingImages.length > 0 ? (
                    <div className="product-form__thumbs">
                        {existingImages.map((image) => (
                            <div key={image.id} className="product-form__thumb">
                                <img src={image.url} alt="" />
                                <button
                                    type="button"
                                    className="admin-table__link is-danger"
                                    onClick={() => removeExistingImage(image.id)}
                                >
                                    Remove
                                </button>
                            </div>
                        ))}
                    </div>
                ) : null}
                <input
                    className="banner-form__input banner-form__file"
                    type="file"
                    accept="image/*"
                    multiple
                    onChange={(event) =>
                        setData('additional_images', Array.from(event.target.files ?? []))
                    }
                />
                {errors.additional_images ? (
                    <span className="banner-form__error">{errors.additional_images}</span>
                ) : null}
            </fieldset>

            <fieldset className="banner-form__fieldset product-options">
                <legend className="banner-form__legend">Sizes</legend>
                <p className="product-form__hint">Add each size once. The variant matrix builds automatically.</p>
                {cleanSizes.length > 0 ? (
                    <div className="product-options__chips" aria-label="Selected sizes">
                        {cleanSizes.map((size) => (
                            <span key={size} className="product-options__chip">
                                <span className="product-options__chip-label">{size}</span>
                                <button
                                    type="button"
                                    className="product-options__chip-remove"
                                    aria-label={`Remove size ${size}`}
                                    onClick={() =>
                                        setSizes(sizes.filter((item) => String(item).trim() !== size))
                                    }
                                >
                                    ×
                                </button>
                            </span>
                        ))}
                    </div>
                ) : (
                    <p className="product-options__empty">No sizes yet. Optional for color-only products.</p>
                )}
                <div className="product-options__add">
                    <input
                        className="banner-form__input"
                        type="text"
                        value={sizeDraft}
                        placeholder="e.g. M"
                        onChange={(event) => setSizeDraft(event.target.value)}
                        onKeyDown={(event) => {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                addSize();
                            }
                        }}
                    />
                    <button type="button" className="btn btn--primary btn--compact" onClick={addSize}>
                        Add size
                    </button>
                </div>
            </fieldset>

            <fieldset className="banner-form__fieldset product-options">
                <legend className="banner-form__legend">Colors</legend>
                <p className="product-form__hint">Name + swatch. Combined with sizes for priced stock rows.</p>
                {cleanColors.length > 0 ? (
                    <div className="product-options__chips" aria-label="Selected colors">
                        {cleanColors.map((color) => (
                            <span key={color.name} className="product-options__chip product-options__chip--color">
                                <svg
                                    className="product-options__swatch"
                                    viewBox="0 0 16 16"
                                    aria-hidden="true"
                                >
                                    <rect width="16" height="16" rx="4" fill={color.hex || '#111827'} />
                                </svg>
                                <span className="product-options__chip-label">{color.name}</span>
                                <button
                                    type="button"
                                    className="product-options__chip-remove"
                                    aria-label={`Remove color ${color.name}`}
                                    onClick={() =>
                                        setColors(
                                            colors.filter(
                                                (item) => String(item.name).trim() !== color.name,
                                            ),
                                        )
                                    }
                                >
                                    ×
                                </button>
                            </span>
                        ))}
                    </div>
                ) : (
                    <p className="product-options__empty">No colors yet. Optional for size-only products.</p>
                )}
                <div className="product-options__add product-options__add--color">
                    <input
                        className="banner-form__input"
                        type="text"
                        value={colorDraft.name}
                        placeholder="Color name"
                        onChange={(event) =>
                            setColorDraft((current) => ({ ...current, name: event.target.value }))
                        }
                        onKeyDown={(event) => {
                            if (event.key === 'Enter') {
                                event.preventDefault();
                                addColor();
                            }
                        }}
                    />
                    <input
                        className="product-form__color"
                        type="color"
                        value={colorDraft.hex || '#111827'}
                        onChange={(event) =>
                            setColorDraft((current) => ({ ...current, hex: event.target.value }))
                        }
                        aria-label="Color swatch"
                    />
                    <button type="button" className="btn btn--primary btn--compact" onClick={addColor}>
                        Add color
                    </button>
                </div>
            </fieldset>

            {hasVariantMatrix ? (
                <fieldset className="banner-form__fieldset">
                    <legend className="banner-form__legend">Variant matrix (price & stock)</legend>
                    <p className="product-form__hint">
                        Each size × color combination is a sellable SKU with its own price and quantity.
                    </p>
                    <div className="product-form__matrix-wrap">
                        <table className="product-form__matrix">
                            <thead>
                                <tr>
                                    {cleanSizes.length > 0 ? <th scope="col">Size</th> : null}
                                    {cleanColors.length > 0 ? <th scope="col">Color</th> : null}
                                    <th scope="col">SKU</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">Sale</th>
                                    <th scope="col">Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                {variants.map((row, index) => (
                                    <tr key={`${row.size}-${row.color}-${index}`}>
                                        {cleanSizes.length > 0 ? <td>{row.size || '—'}</td> : null}
                                        {cleanColors.length > 0 ? <td>{row.color || '—'}</td> : null}
                                        <td>
                                            <input
                                                className="banner-form__input"
                                                type="text"
                                                value={row.sku}
                                                onChange={(event) =>
                                                    updateVariant(index, 'sku', event.target.value)
                                                }
                                            />
                                        </td>
                                        <td>
                                            <input
                                                className="banner-form__input"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={row.price}
                                                onChange={(event) =>
                                                    updateVariant(index, 'price', event.target.value)
                                                }
                                                required
                                            />
                                        </td>
                                        <td>
                                            <input
                                                className="banner-form__input"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value={row.sale_price}
                                                onChange={(event) =>
                                                    updateVariant(index, 'sale_price', event.target.value)
                                                }
                                            />
                                        </td>
                                        <td>
                                            <input
                                                className="banner-form__input"
                                                type="number"
                                                min="0"
                                                value={row.stock}
                                                onChange={(event) =>
                                                    updateVariant(index, 'stock', Number(event.target.value))
                                                }
                                                required
                                            />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                    {errors.variants ? (
                        <span className="banner-form__error">{errors.variants}</span>
                    ) : null}
                </fieldset>
            ) : null}

            <div className="product-form__grid banner-form__grid--compact">
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

                <label className="banner-form__check product-form__switch">
                    <input
                        type="checkbox"
                        checked={data.is_unlaunched}
                        onChange={(event) => setData('is_unlaunched', event.target.checked)}
                    />
                    Save as draft (unlaunched / hidden from storefront)
                </label>
            </div>

            <div className="banner-form__actions product-form__actions">
                <button
                    type="button"
                    className="btn btn--ghost"
                    onClick={() => setPreviewOpen(true)}
                >
                    Preview
                </button>
                <button type="submit" className="btn btn--primary" disabled={processing}>
                    {processing
                        ? 'Saving...'
                        : data.is_unlaunched
                          ? 'Save draft'
                          : submitLabel.includes('Create')
                            ? 'Publish Product'
                            : 'Publish changes'}
                </button>
                <Link href={cancelUrl} className="btn btn--ghost">
                    Cancel
                </Link>
            </div>
        </form>

        <ProductPreview
            open={previewOpen}
            onClose={() => setPreviewOpen(false)}
            name={data.name}
            price={data.price}
            salePrice={data.sale_price}
            shortDescription={data.short_description}
            description={data.description}
            imageUrl={mainPreviewUrl}
            sizes={cleanSizes}
            colors={cleanColors}
            sku={data.sku}
        />
        </>
    );
}

function buildCombinations(sizes, colors) {
    if (sizes.length > 0 && colors.length > 0) {
        const rows = [];
        sizes.forEach((size) => {
            colors.forEach((color) => {
                rows.push({ size, color });
            });
        });
        return rows;
    }

    if (sizes.length > 0) {
        return sizes.map((size) => ({ size, color: '' }));
    }

    if (colors.length > 0) {
        return colors.map((color) => ({ size: '', color }));
    }

    return [];
}

function slugify(value) {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}
