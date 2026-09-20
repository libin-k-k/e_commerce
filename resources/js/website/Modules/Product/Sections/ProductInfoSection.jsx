import { useMemo, useState } from 'react';

export default function ProductInfoSection({ product, onVariantChange }) {
    const sizes = product.sizes ?? [];
    const colors = product.colors ?? [];
    const variants = product.variants ?? [];
    const threshold = product.lowStockThreshold ?? 10;

    const [selectedSize, setSelectedSize] = useState(sizes[0] ?? null);
    const [selectedColor, setSelectedColor] = useState(colors[0] ?? null);

    const selectedVariant = useMemo(() => {
        if (variants.length === 0) {
            return null;
        }

        return (
            variants.find((variant) => {
                const sizeOk = sizes.length === 0 || variant.size === selectedSize;
                const colorOk = colors.length === 0 || variant.color === selectedColor;

                return sizeOk && colorOk;
            }) ?? null
        );
    }, [variants, sizes.length, colors.length, selectedSize, selectedColor]);

    const activeStock = selectedVariant?.stock ?? product.stock ?? 0;
    const inStock = selectedVariant ? selectedVariant.inStock : Boolean(product.inStock);
    const lowStock = selectedVariant
        ? Boolean(selectedVariant.lowStock)
        : Boolean(product.lowStock) || (activeStock > 0 && activeStock <= threshold);
    const priceLabel = selectedVariant
        ? formatMoney(selectedVariant.effectivePrice)
        : product.price;
    const compareLabel =
        selectedVariant &&
        selectedVariant.salePrice != null &&
        selectedVariant.salePrice < selectedVariant.price
            ? formatMoney(selectedVariant.price)
            : product.compareAtPrice;

    const selectSize = (size) => {
        setSelectedSize(size);
        const match = variants.find((variant) => {
            const sizeOk = sizes.length === 0 || variant.size === size;
            const colorOk = colors.length === 0 || variant.color === selectedColor;
            return sizeOk && colorOk;
        });
        onVariantChange?.({ size, color: selectedColor, variantId: match?.id ?? null });
    };

    const selectColor = (color) => {
        setSelectedColor(color);
        const match = variants.find((variant) => {
            const sizeOk = sizes.length === 0 || variant.size === selectedSize;
            const colorOk = colors.length === 0 || variant.color === color;
            return sizeOk && colorOk;
        });
        onVariantChange?.({ size: selectedSize, color, variantId: match?.id ?? null });
    };

    return (
        <section className="pdp-info" aria-labelledby="pdp-title">
            {product.badge ? <p className="pdp-info__badge">{product.badge}</p> : null}
            <h1 id="pdp-title" className="pdp-info__title">
                {product.name}
            </h1>

            <div className="pdp-info__meta">
                <p className="pdp-info__rating">
                    Rated {product.rating} · {product.reviewCount} reviews
                </p>
                <p className={`pdp-info__stock${inStock ? '' : ' is-out'}`}>
                    {inStock ? 'In stock' : 'Out of stock'}
                </p>
            </div>

            <div className="pdp-info__price-row">
                <p className="pdp-info__price">{priceLabel}</p>
                {compareLabel ? <p className="pdp-info__compare">{compareLabel}</p> : null}
            </div>

            {inStock && lowStock ? (
                <p className="stock-alert" role="alert">
                    Only {activeStock} left - order soon before it sells out.
                </p>
            ) : null}

            {product.shortDescription ? (
                <p className="pdp-info__short">{product.shortDescription}</p>
            ) : null}

            {colors.length > 0 ? (
                <div className="pdp-options">
                    <p className="pdp-options__label">Color</p>
                    <div className="pdp-options__list">
                        {colors.map((color) => (
                            <button
                                key={color}
                                type="button"
                                className={`pdp-chip${selectedColor === color ? ' is-active' : ''}`}
                                onClick={() => selectColor(color)}
                            >
                                {color}
                            </button>
                        ))}
                    </div>
                </div>
            ) : null}

            {sizes.length > 0 ? (
                <div className="pdp-options">
                    <p className="pdp-options__label">Size</p>
                    <div className="pdp-options__list">
                        {sizes.map((size) => (
                            <button
                                key={size}
                                type="button"
                                className={`pdp-chip${selectedSize === size ? ' is-active' : ''}`}
                                onClick={() => selectSize(size)}
                            >
                                {size}
                            </button>
                        ))}
                    </div>
                </div>
            ) : null}
        </section>
    );
}

function formatMoney(amount) {
    return `₹${Number(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}
