import { Link, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import ProductStickyActions from '../Components/ProductStickyActions';
import ProductDescriptionSection from '../Sections/ProductDescriptionSection';
import ProductGallerySection from '../Sections/ProductGallerySection';
import ProductInfoSection from '../Sections/ProductInfoSection';
import RelatedProductsSection from '../Sections/RelatedProductsSection';

export default function Show({ seo, product, related = [] }) {
    const { flash, wishlistProductIds = [] } = usePage().props;
    const [selectionInStock, setSelectionInStock] = useState(Boolean(product.inStock));
    const [selectedVariantId, setSelectedVariantId] = useState(() => product.variants?.[0]?.id ?? null);
    const wished = wishlistProductIds.includes(product.id);

    const onVariantChange = ({ size, color, variantId }) => {
        const variants = product.variants ?? [];
        if (variants.length === 0) {
            setSelectionInStock(Boolean(product.inStock));
            setSelectedVariantId(null);
            return;
        }

        const match = variants.find((variant) => {
            if (variantId != null) {
                return variant.id === variantId;
            }
            const sizeOk = (product.sizes ?? []).length === 0 || variant.size === size;
            const colorOk = (product.colors ?? []).length === 0 || variant.color === color;
            return sizeOk && colorOk;
        });

        setSelectionInStock(Boolean(match?.inStock));
        setSelectedVariantId(match?.id ?? null);
    };

    const addToCart = () => {
        router.post(
            '/cart',
            {
                product_id: product.id,
                product_variant_id: selectedVariantId,
                quantity: 1,
            },
            { preserveScroll: true },
        );
    };

    const buyNow = () => {
        router.post('/cart', {
            product_id: product.id,
            product_variant_id: selectedVariantId,
            quantity: 1,
            buy_now: true,
        });
    };

    const toggleWishlist = () => {
        router.post('/wishlist', { product_id: product.id }, { preserveScroll: true });
    };

    const feedback = useMemo(() => flash?.success ?? '', [flash?.success]);

    return (
        <StorefrontLayout seo={seo} current="products">
            <div className="pdp">
                <div className="pdp__topbar">
                    <Link href="/products" className="pdp__back" aria-label="Back to products">
                        <BackIcon />
                        <span>Back</span>
                    </Link>
                    <button
                        type="button"
                        className={`icon-btn icon-btn--wishlist${wished ? ' is-active' : ''}`}
                        aria-label={wished ? 'Remove from wishlist' : 'Add to wishlist'}
                        onClick={toggleWishlist}
                    >
                        <HeartIcon filled={wished} />
                    </button>
                </div>

                <div className="pdp__layout">
                    <ProductGallerySection images={product.images} name={product.name} />
                    <div className="pdp__summary">
                        <ProductInfoSection product={product} onVariantChange={onVariantChange} />
                        <ProductDescriptionSection
                            description={product.description}
                            specs={product.specs}
                            shortDescription={product.shortDescription}
                        />
                    </div>
                </div>

                {feedback ? <p className="pdp-feedback" role="status">{feedback}</p> : null}
            </div>

            <RelatedProductsSection products={related} />

            <ProductStickyActions
                inStock={selectionInStock}
                onAddToCart={addToCart}
                onBuyNow={buyNow}
            />
        </StorefrontLayout>
    );
}

function BackIcon() {
    return (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M15 6l-6 6 6 6" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
        </svg>
    );
}

function HeartIcon({ filled = false }) {
    return (
        <svg width="22" height="22" viewBox="0 0 24 24" fill={filled ? 'currentColor' : 'none'} aria-hidden="true">
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
