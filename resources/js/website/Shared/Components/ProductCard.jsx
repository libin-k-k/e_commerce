import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

const MAX_SWATCHES = 3;

const compactCount = (count) =>
    new Intl.NumberFormat('en-IN', { notation: 'compact', maximumFractionDigits: 1 }).format(count);

export default function ProductCard({ product, titleAs = 'h3', variant = 'default' }) {
    const href = `/products/${product.slug}`;
    const TitleTag = titleAs;
    const { wishlistProductIds = [] } = usePage().props;
    const wished = wishlistProductIds.includes(product.id);
    const [adding, setAdding] = useState(false);
    const [added, setAdded] = useState(false);

    const swatches = product.colorOptions ?? [];
    const needsOptions = (product.variants ?? []).length > 0;
    const rating = Number(product.rating ?? 0);
    const reviewCount = Number(product.reviewCount ?? 0);

    useEffect(() => {
        if (!added) {
            return undefined;
        }

        const timer = window.setTimeout(() => setAdded(false), 1600);

        return () => window.clearTimeout(timer);
    }, [added]);

    const onAddToCart = (event) => {
        event.preventDefault();
        event.stopPropagation();
        router.post(
            '/cart',
            { product_id: product.id, quantity: 1 },
            {
                preserveScroll: true,
                onStart: () => setAdding(true),
                onSuccess: () => setAdded(true),
                onFinish: () => setAdding(false),
            },
        );
    };

    const onWishlist = (event) => {
        event.preventDefault();
        event.stopPropagation();
        router.post('/wishlist', { product_id: product.id }, { preserveScroll: true });
    };

    const compact = variant === 'compact';
    const cartClass = `product-card__cart${compact ? ' product-card__cart--icon' : ''}`;

    let cartAction;

    if (product.inStock === false) {
        cartAction = (
            <button
                type="button"
                className={cartClass}
                disabled
                aria-label={compact ? `${product.name} is out of stock` : undefined}
            >
                {compact ? <CartIcon /> : 'Out of stock'}
            </button>
        );
    } else if (needsOptions) {
        cartAction = (
            <Link href={href} className={cartClass} aria-label={compact ? `Choose options for ${product.name}` : undefined}>
                <CartIcon />
                {compact ? null : 'Add to Cart'}
            </Link>
        );
    } else {
        cartAction = (
            <button
                type="button"
                className={`${cartClass}${added ? ' is-added' : ''}`}
                disabled={adding}
                aria-label={compact ? (added ? `${product.name} added to cart` : `Add ${product.name} to cart`) : undefined}
                onClick={onAddToCart}
            >
                {added ? <CheckIcon /> : <CartIcon />}
                {compact ? null : added ? 'Added' : 'Add to Cart'}
            </button>
        );
    }

    const priceRow = (
        <Link href={href} className="product-card__price-row" aria-label={`View ${product.name}`}>
            <span className="product-card__price">{product.price}</span>
            {product.compareAtPrice ? <span className="product-card__compare">{product.compareAtPrice}</span> : null}
        </Link>
    );

    return (
        <article className={`product-card${compact ? ' product-card--compact' : ''}`}>
            <div className="product-card__media-wrap">
                <Link href={href} className="product-card__media-link" aria-label={product.name}>
                    <ProductBadge product={product} />
                    {product.image ? (
                        <img className="product-card__media" src={product.image} alt="" loading="lazy" />
                    ) : (
                        <span className="product-card__media" aria-hidden="true" />
                    )}
                </Link>
                {compact ? null : (
                    <button
                        type="button"
                        className={`product-card__wishlist${wished ? ' is-active' : ''}`}
                        aria-label={wished ? `Remove ${product.name} from wishlist` : `Add ${product.name} to wishlist`}
                        title={wished ? 'Remove from wishlist' : 'Add to wishlist'}
                        onClick={onWishlist}
                    >
                        <WishlistIcon filled={wished} />
                    </button>
                )}
            </div>

            <div className="product-card__body">
                {!compact && swatches.length > 0 ? (
                    <ul className="product-card__swatches" aria-label={`${swatches.length} colours`}>
                        {swatches.slice(0, MAX_SWATCHES).map((swatch) => (
                            <li
                                key={swatch.name}
                                className="product-card__swatch"
                                style={swatch.hex ? { '--swatch': swatch.hex } : undefined}
                                title={swatch.name}
                            />
                        ))}
                        {swatches.length > MAX_SWATCHES ? (
                            <li className="product-card__swatch-more">+{swatches.length - MAX_SWATCHES}</li>
                        ) : null}
                    </ul>
                ) : null}

                <TitleTag className="product-card__title">
                    <Link href={href}>{product.name}</Link>
                </TitleTag>

                {rating > 0 ? (
                    <p className="product-card__rating" aria-label={`Rated ${rating.toFixed(1)} out of 5`}>
                        <StarIcon />
                        <span className="product-card__rating-value">{rating.toFixed(1)}</span>
                        {reviewCount > 0 ? (
                            <span className="product-card__rating-count">({compactCount(reviewCount)})</span>
                        ) : null}
                    </p>
                ) : null}

                {compact ? (
                    <div className="product-card__buy-row">
                        {priceRow}
                        {cartAction}
                    </div>
                ) : (
                    <>
                        {priceRow}
                        <div className="product-card__actions">{cartAction}</div>
                    </>
                )}
            </div>
        </article>
    );
}

function ProductBadge({ product }) {
    if (product.discountPercent) {
        return <span className="product-card__badge">{product.discountPercent}% off</span>;
    }

    if (product.isNew) {
        return <span className="product-card__badge product-card__badge--new">New</span>;
    }

    return null;
}

function WishlistIcon({ filled = false }) {
    return (
        <svg width="16" height="16" viewBox="0 0 24 24" fill={filled ? 'currentColor' : 'none'} aria-hidden="true">
            <path
                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
            />
        </svg>
    );
}

function StarIcon() {
    return (
        <svg className="product-card__star" width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M12 2.5l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5L12 17.3l-5.8 3.1 1.1-6.5L2.6 9.3l6.5-.9L12 2.5z" />
        </svg>
    );
}

function CartIcon() {
    return (
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 6h15l-1.5 9h-12L6 6z" stroke="currentColor" strokeWidth="2" strokeLinejoin="round" />
            <path d="M6 6L5 3H2" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            <circle cx="9" cy="20" r="1.5" fill="currentColor" />
            <circle cx="17" cy="20" r="1.5" fill="currentColor" />
        </svg>
    );
}

function CheckIcon() {
    return (
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}
