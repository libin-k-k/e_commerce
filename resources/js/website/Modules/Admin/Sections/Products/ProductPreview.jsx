import { useEffect, useMemo, useState } from 'react';

/**
 * Pre-publish product preview with desktop / mobile frames.
 */
export default function ProductPreview({
    open,
    onClose,
    name,
    price,
    salePrice,
    shortDescription,
    description,
    imageUrl,
    sizes = [],
    colors = [],
    sku,
}) {
    const [mode, setMode] = useState('desktop');

    useEffect(() => {
        if (!open) {
            return undefined;
        }

        const onKey = (event) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        document.addEventListener('keydown', onKey);
        document.body.classList.add('is-preview-open');

        return () => {
            document.removeEventListener('keydown', onKey);
            document.body.classList.remove('is-preview-open');
        };
    }, [open, onClose]);

    const priceLabel = useMemo(() => {
        const amount = salePrice !== '' && salePrice != null && Number(salePrice) < Number(price)
            ? Number(salePrice)
            : Number(price || 0);

        return formatMoney(amount);
    }, [price, salePrice]);

    const compareLabel = useMemo(() => {
        if (salePrice === '' || salePrice == null || Number(salePrice) >= Number(price || 0)) {
            return null;
        }

        return formatMoney(Number(price || 0));
    }, [price, salePrice]);

    if (!open) {
        return null;
    }

    return (
        <div className="product-preview" role="dialog" aria-modal="true" aria-labelledby="product-preview-title">
            <div className="product-preview__backdrop" onClick={onClose} aria-hidden="true" />
            <div className="product-preview__panel">
                <div className="product-preview__toolbar">
                    <div>
                        <h2 id="product-preview-title" className="product-preview__title">
                            Product preview
                        </h2>
                        <p className="product-preview__meta">Review before publish · not live yet</p>
                    </div>
                    <div className="product-preview__modes" role="group" aria-label="Preview device">
                        <button
                            type="button"
                            className={`product-preview__mode${mode === 'desktop' ? ' is-active' : ''}`}
                            onClick={() => setMode('desktop')}
                        >
                            Desktop
                        </button>
                        <button
                            type="button"
                            className={`product-preview__mode${mode === 'mobile' ? ' is-active' : ''}`}
                            onClick={() => setMode('mobile')}
                        >
                            Mobile
                        </button>
                    </div>
                    <button type="button" className="btn btn--ghost btn--compact" onClick={onClose}>
                        Close
                    </button>
                </div>

                <div className={`product-preview__stage is-${mode}`}>
                    <div className={`product-preview__frame product-preview__frame--${mode}`}>
                        <article className="product-preview__card">
                            <div className="product-preview__media">
                                {imageUrl ? (
                                    <img src={imageUrl} alt="" />
                                ) : (
                                    <div className="product-preview__media-empty">No main image</div>
                                )}
                            </div>

                            <div className="product-preview__body">
                                {sku ? <p className="product-preview__sku">{sku}</p> : null}
                                <h3 className="product-preview__name">{name?.trim() || 'Product name'}</h3>

                                <div className="product-preview__price-row">
                                    <span className="product-preview__price">{priceLabel}</span>
                                    {compareLabel ? (
                                        <span className="product-preview__compare">{compareLabel}</span>
                                    ) : null}
                                </div>

                                {shortDescription ? (
                                    <p className="product-preview__short">{shortDescription}</p>
                                ) : null}

                                {colors.length > 0 ? (
                                    <div className="product-preview__options">
                                        <p className="product-preview__label">Colors</p>
                                        <div className="product-preview__chips">
                                            {colors.map((color) => (
                                                <span key={color.name} className="product-preview__chip">
                                                    <svg
                                                        className="product-preview__swatch"
                                                        viewBox="0 0 16 16"
                                                        aria-hidden="true"
                                                    >
                                                        <rect
                                                            width="16"
                                                            height="16"
                                                            rx="4"
                                                            fill={color.hex || '#cccccc'}
                                                        />
                                                    </svg>
                                                    {color.name}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                ) : null}

                                {sizes.length > 0 ? (
                                    <div className="product-preview__options">
                                        <p className="product-preview__label">Sizes</p>
                                        <div className="product-preview__chips">
                                            {sizes.map((size) => (
                                                <span key={size} className="product-preview__chip">
                                                    {size}
                                                </span>
                                            ))}
                                        </div>
                                    </div>
                                ) : null}

                                {description ? (
                                    <div className="product-preview__details">
                                        <p className="product-preview__label">Description</p>
                                        <div
                                            className="pdp-details__html product-preview__html"
                                            dangerouslySetInnerHTML={{ __html: description }}
                                        />
                                    </div>
                                ) : (
                                    <p className="product-preview__empty">No description yet.</p>
                                )}
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    );
}

function formatMoney(amount) {
    return `₹${Number(amount || 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}
