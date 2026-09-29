import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';
import StorefrontLayout from '../../../Shared/Layouts/StorefrontLayout';
import ListingHeader from '../Components/ListingHeader';
import ProductDeliveryCard from '../Components/ProductDeliveryCard';
import ProductDetailsSection from '../Sections/ProductDetailsSection';
import ProductGallerySection from '../Sections/ProductGallerySection';
import ProductInfoSection from '../Sections/ProductInfoSection';
import RelatedProductsSection from '../Sections/RelatedProductsSection';

const MAX_QUANTITY = 99;

export default function Show({ seo, product, related = [], deliveryAddress = null, faqs = [] }) {
    const { flash, wishlistProductIds = [] } = usePage().props;
    const sizes = product.sizes ?? [];
    const colors = product.colors ?? [];
    const variants = product.variants ?? [];
    const hasVariants = variants.length > 0;

    const [selectedSize, setSelectedSize] = useState(sizes[0] ?? null);
    const [selectedColor, setSelectedColor] = useState(colors[0] ?? null);
    const [quantity, setQuantity] = useState(1);
    const [cartState, setCartState] = useState('idle');
    const wished = wishlistProductIds.includes(product.id);

    const selectedVariant = useMemo(() => {
        if (!hasVariants) {
            return null;
        }

        return (
            variants.find(
                (variant) =>
                    (sizes.length === 0 || variant.size === selectedSize) &&
                    (colors.length === 0 || variant.color === selectedColor),
            ) ?? null
        );
    }, [hasVariants, variants, sizes.length, colors.length, selectedSize, selectedColor]);

    const stock = hasVariants ? (selectedVariant?.stock ?? 0) : (product.stock ?? 0);
    const inStock = hasVariants ? Boolean(selectedVariant?.inStock) : Boolean(product.inStock);
    const lowStock = hasVariants ? Boolean(selectedVariant?.lowStock) : Boolean(product.lowStock);
    const maxQuantity = Math.max(1, Math.min(MAX_QUANTITY, stock));
    const price = selectedVariant ? selectedVariant.effectivePrice : product.priceValue;
    const compareAt = selectedVariant
        ? selectedVariant.salePrice != null && selectedVariant.salePrice < selectedVariant.price
            ? selectedVariant.price
            : null
        : (product.compareAtValue ?? null);
    const discountPercent = compareAt !== null && compareAt > price ? Math.round((1 - price / compareAt) * 100) : 0;
    const badge =
        discountPercent > 0
            ? { label: `${discountPercent}% off`, isNew: false }
            : product.isNew
              ? { label: 'New', isNew: true }
              : null;

    useEffect(() => {
        setQuantity((current) => Math.min(current, maxQuantity));
    }, [maxQuantity]);

    useEffect(() => {
        if (cartState !== 'added') {
            return undefined;
        }

        const timer = window.setTimeout(() => setCartState('idle'), 1600);

        return () => window.clearTimeout(timer);
    }, [cartState]);

    const cartPayload = () => ({
        product_id: product.id,
        product_variant_id: selectedVariant?.id ?? null,
        quantity,
    });

    const addToCart = () => {
        router.post('/cart', cartPayload(), {
            preserveScroll: true,
            onStart: () => setCartState('adding'),
            onSuccess: () => setCartState('added'),
            onError: () => setCartState('idle'),
        });
    };

    const buyNow = () => {
        router.post('/cart', { ...cartPayload(), buy_now: true });
    };

    const toggleWishlist = () => {
        router.post('/wishlist', { product_id: product.id }, { preserveScroll: true });
    };

    const categoryHref = product.subcategory
        ? `/products?category=${product.subcategory}`
        : product.category
          ? `/products?category=${product.category}`
          : '/products';

    return (
        <StorefrontLayout seo={seo} current="products" hideHeaderOnMobile>
            <ListingHeader
                className="listing-header--pdp"
                title="Product Details"
                titleAs="p"
            />

            <div className="pdp">
                <nav className="listing-breadcrumb pdp__breadcrumb" aria-label="Breadcrumb">
                    <ol>
                        <li>
                            <Link href="/">Home</Link>
                        </li>
                        {product.category ? (
                            <li>
                                <Link href={`/products?category=${product.category}`}>{product.categoryName}</Link>
                            </li>
                        ) : null}
                        {product.subcategory ? (
                            <li>
                                <Link href={`/products?category=${product.subcategory}`}>{product.subcategoryName}</Link>
                            </li>
                        ) : null}
                        <li aria-current="page">{product.name}</li>
                    </ol>
                </nav>

                <div className="pdp__layout">
                    <ProductGallerySection
                        images={product.images}
                        name={product.name}
                        badge={badge}
                        wished={wished}
                        onToggleWishlist={toggleWishlist}
                    />

                    <ProductInfoSection
                        product={product}
                        badge={badge}
                        selectedSize={selectedSize}
                        selectedColor={selectedColor}
                        onSelectSize={setSelectedSize}
                        onSelectColor={setSelectedColor}
                        price={price}
                        compareAt={compareAt}
                        stock={stock}
                        inStock={inStock}
                        lowStock={lowStock}
                        quantity={quantity}
                        maxQuantity={maxQuantity}
                        onQuantityChange={setQuantity}
                        cartState={cartState}
                        onAddToCart={addToCart}
                        onBuyNow={buyNow}
                    />

                    <ProductDetailsSection product={product} faqs={faqs} />

                    <aside className="pdp__aside">
                        <ProductDeliveryCard address={deliveryAddress} />
                        <RelatedProductsSection products={related} seeAllHref={categoryHref} />
                    </aside>
                </div>

                {flash?.success ? (
                    <p className="pdp-feedback" role="status">
                        {flash.success}
                    </p>
                ) : null}
            </div>
        </StorefrontLayout>
    );
}
