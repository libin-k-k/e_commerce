const TRUST_POINTS = [
    { icon: 'delivery', title: 'Fast Delivery', text: 'Ships in 48 hrs' },
    { icon: 'returns', title: 'Easy Returns', text: '7 days policy' },
    { icon: 'secure', title: 'Secure Payment', text: '100% safe' },
    { icon: 'cod', title: 'Cash on Delivery', text: 'Where available' },
];

export function formatMoney(amount) {
    return `₹${Number(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

export default function ProductInfoSection({
    product,
    badge,
    selectedSize,
    selectedColor,
    onSelectSize,
    onSelectColor,
    price,
    compareAt,
    stock,
    inStock,
    lowStock,
    quantity,
    maxQuantity,
    onQuantityChange,
    cartState,
    onAddToCart,
    onBuyNow,
}) {
    const sizes = product.sizes ?? [];
    const colorOptions =
        (product.colorOptions ?? []).length > 0
            ? product.colorOptions
            : (product.colors ?? []).map((name) => ({ name, hex: null }));
    const savings = compareAt !== null && compareAt > price ? compareAt - price : 0;
    const savingsPercent = savings > 0 ? Math.round((savings / compareAt) * 100) : 0;
    const rating = Number(product.rating ?? 0);
    const reviewCount = product.reviewCount ?? 0;

    return (
        <section className="pdp-info" aria-labelledby="pdp-title">
            {badge ? <p className={`pdp-info__badge${badge.isNew ? ' is-new' : ''}`}>{badge.label}</p> : null}
            <h1 id="pdp-title" className="pdp-info__title">
                {product.name}
            </h1>

            <p className="pdp-info__meta">
                {product.sku ? (
                    <span>
                        Product Code: <strong>#{product.sku}</strong>
                    </span>
                ) : null}
                {product.categoryName ? (
                    <span>
                        Category: <strong>{product.subcategoryName ?? product.categoryName}</strong>
                    </span>
                ) : null}
            </p>

            {rating > 0 ? (
                <p className="pdp-info__rating">
                    <StarIcon />
                    <strong>{rating.toFixed(1)}</strong>
                    {reviewCount > 0 ? (
                        <span>({reviewCount.toLocaleString('en-IN')} reviews)</span>
                    ) : (
                        <span>Customer rating</span>
                    )}
                </p>
            ) : null}

            <div className="pdp-info__price-row">
                <p className="pdp-info__price">{formatMoney(price)}</p>
                {savings > 0 ? (
                    <>
                        <p className="pdp-info__compare">{formatMoney(compareAt)}</p>
                        <p className="pdp-info__save">
                            Save {formatMoney(savings)} ({savingsPercent}%)
                        </p>
                    </>
                ) : null}
            </div>

            {colorOptions.length > 0 ? (
                <div className="pdp-options">
                    <p className="pdp-options__label">
                        Color: <span>{selectedColor}</span>
                    </p>
                    <div className="pdp-options__list">
                        {colorOptions.map((color) => (
                            <button
                                key={color.name}
                                type="button"
                                className={`pdp-color${selectedColor === color.name ? ' is-active' : ''}`}
                                aria-pressed={selectedColor === color.name}
                                onClick={() => onSelectColor(color.name)}
                            >
                                <span
                                    className="pdp-color__swatch"
                                    style={color.hex ? { '--swatch': color.hex } : undefined}
                                    aria-hidden="true"
                                />
                                {color.name}
                            </button>
                        ))}
                    </div>
                </div>
            ) : null}

            {sizes.length > 0 ? (
                <div className="pdp-options">
                    <p className="pdp-options__label">
                        Size: <span>{selectedSize}</span>
                    </p>
                    <div className="pdp-options__list">
                        {sizes.map((size) => (
                            <button
                                key={size}
                                type="button"
                                className={`pdp-size${selectedSize === size ? ' is-active' : ''}`}
                                aria-pressed={selectedSize === size}
                                onClick={() => onSelectSize(size)}
                            >
                                {size}
                            </button>
                        ))}
                    </div>
                </div>
            ) : null}

            <div className="pdp-options">
                <p className="pdp-options__label">Quantity:</p>
                <div className="pdp-qty-row">
                    <div className="pdp-qty" role="group" aria-label="Quantity">
                        <button
                            type="button"
                            className="pdp-qty__btn"
                            aria-label="Decrease quantity"
                            disabled={!inStock || quantity <= 1}
                            onClick={() => onQuantityChange(quantity - 1)}
                        >
                            <MinusIcon />
                        </button>
                        <span className="pdp-qty__value" aria-live="polite">
                            {quantity}
                        </span>
                        <button
                            type="button"
                            className="pdp-qty__btn"
                            aria-label="Increase quantity"
                            disabled={!inStock || quantity >= maxQuantity}
                            onClick={() => onQuantityChange(quantity + 1)}
                        >
                            <PlusIcon />
                        </button>
                    </div>
                    <p className={`pdp-stock${!inStock ? ' is-out' : lowStock ? ' is-low' : ''}`}>
                        <StockIcon inStock={inStock} />
                        {!inStock ? 'Out of Stock' : lowStock ? `Only ${stock} left` : 'In Stock'}
                    </p>
                </div>
            </div>

            <div className="pdp-actions">
                <button
                    type="button"
                    className={`pdp-actions__btn pdp-actions__btn--cart${cartState === 'added' ? ' is-added' : ''}`}
                    disabled={!inStock || cartState === 'adding'}
                    onClick={onAddToCart}
                >
                    <CartIcon />
                    {cartState === 'added' ? 'Added to Cart' : 'Add to Cart'}
                </button>
                <button
                    type="button"
                    className="pdp-actions__btn pdp-actions__btn--buy"
                    disabled={!inStock}
                    onClick={onBuyNow}
                >
                    <BoltIcon />
                    Buy Now
                </button>
            </div>

            <ul className="pdp-trust">
                {TRUST_POINTS.map((point) => (
                    <li key={point.icon} className="pdp-trust__item">
                        <span className="pdp-trust__icon" aria-hidden="true">
                            <TrustIcon name={point.icon} />
                        </span>
                        <span className="pdp-trust__copy">
                            <strong>{point.title}</strong>
                            <span>{point.text}</span>
                        </span>
                    </li>
                ))}
            </ul>
        </section>
    );
}

function TrustIcon({ name }) {
    const paths = {
        delivery: (
            <>
                <path d="M3 6h11v9H3zM14 9h4l3 3v3h-7z" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
                <circle cx="7" cy="17.5" r="1.8" stroke="currentColor" strokeWidth="1.8" />
                <circle cx="17" cy="17.5" r="1.8" stroke="currentColor" strokeWidth="1.8" />
            </>
        ),
        returns: (
            <>
                <path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
                <path d="M4 7.5l8 4.5 8-4.5M12 12v9" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
            </>
        ),
        secure: (
            <>
                <path d="M12 3l8 3v6c0 4.5-3.4 8.2-8 9-4.6-.8-8-4.5-8-9V6l8-3z" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
                <path d="M8.5 12l2.5 2.5 4.5-5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </>
        ),
        cod: (
            <>
                <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="1.8" />
                <path d="M9 8h6M9 11h6M13 8c1.7 0 2 3 0 3h-4l4.5 5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </>
        ),
    };

    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            {paths[name]}
        </svg>
    );
}

function StarIcon() {
    return (
        <svg className="pdp-info__star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9L12 2.5z" />
        </svg>
    );
}

function StockIcon({ inStock }) {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <circle cx="12" cy="12" r="10" />
            <path
                d={inStock ? 'M7.5 12.5l3 3 6-6.5' : 'M8.5 8.5l7 7M15.5 8.5l-7 7'}
                stroke="#fff"
                strokeWidth="2.2"
                strokeLinecap="round"
                strokeLinejoin="round"
                fill="none"
            />
        </svg>
    );
}

function MinusIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12h14" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" />
        </svg>
    );
}

function PlusIcon() {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 5v14M5 12h14" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" />
        </svg>
    );
}

function CartIcon() {
    return (
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6h15l-1.5 9h-12L6 6z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" />
            <path d="M6 6L5 3H2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <circle cx="9" cy="20" r="1.5" fill="currentColor" />
            <circle cx="17" cy="20" r="1.5" fill="currentColor" />
        </svg>
    );
}

function BoltIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M13 2L4 14h7l-1 8 9-12h-7l1-8z" />
        </svg>
    );
}
