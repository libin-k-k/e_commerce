import { Link, router, usePage } from '@inertiajs/react';

export default function ProductCard({ product, titleAs = 'h3' }) {
    const href = `/products/${product.slug}`;
    const TitleTag = titleAs;
    const { wishlistProductIds = [] } = usePage().props;
    const wished = wishlistProductIds.includes(product.id);

    const onBuyNow = (event) => {
        event.preventDefault();
        event.stopPropagation();
        router.post(
            '/cart',
            { product_id: product.id, quantity: 1, buy_now: true },
            { preserveScroll: true },
        );
    };

    const onWishlist = (event) => {
        event.preventDefault();
        event.stopPropagation();
        router.post('/wishlist', { product_id: product.id }, { preserveScroll: true });
    };

    return (
        <article className="product-card">
            <div className="product-card__media-wrap">
                <Link href={href} className="product-card__media-link" aria-label={product.name}>
                    {product.badge ? (
                        <span className="product-card__badge">{product.badge}</span>
                    ) : null}
                    {product.image ? (
                        <img
                            className="product-card__media"
                            src={product.image}
                            alt=""
                            loading="lazy"
                        />
                    ) : (
                        <span className="product-card__media" aria-hidden="true" />
                    )}
                </Link>
                <button
                    type="button"
                    className={`product-card__wishlist${wished ? ' is-active' : ''}`}
                    aria-label={wished ? `Remove ${product.name} from wishlist` : `Add ${product.name} to wishlist`}
                    title={wished ? 'Remove from wishlist' : 'Add to wishlist'}
                    onClick={onWishlist}
                >
                    <WishlistIcon filled={wished} />
                </button>
            </div>

            <div className="product-card__body">
                <TitleTag className="product-card__title">
                    <Link href={href}>{product.name}</Link>
                </TitleTag>

                <Link href={href} className="product-card__price-row" aria-label={`View ${product.name}`}>
                    <span className="product-card__price">{product.price}</span>
                    {product.compareAtPrice ? (
                        <span className="product-card__compare">{product.compareAtPrice}</span>
                    ) : null}
                </Link>

                <div className="product-card__actions">
                    <button
                        type="button"
                        className="btn btn--primary btn--block product-card__buy"
                        disabled={product.inStock === false}
                        onClick={onBuyNow}
                    >
                        Buy now
                    </button>
                </div>
            </div>
        </article>
    );
}

function WishlistIcon({ filled = false }) {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill={filled ? 'currentColor' : 'none'} aria-hidden="true">
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
