import { useCallback, useEffect, useState } from 'react';
import { usePage } from '@inertiajs/react';
import AppPromoBanner from '../Components/AppPromoBanner';
import BottomNav from '../Components/BottomNav';
import CartSheet from '../Components/CartSheet';
import CategoryDrawer from '../Components/CategoryDrawer';
import Footer from '../Components/Footer';
import Header from '../Components/Header';
import SeoHead from '../Components/SeoHead';
import WishlistSheet from '../Components/WishlistSheet';

export default function StorefrontLayout({
    children,
    seo,
    current = 'home',
    hideFooter = false,
    hideHeaderOnMobile = false,
    compactMain = false,
}) {
    const { flash } = usePage().props;
    const [categoriesOpen, setCategoriesOpen] = useState(false);
    const [cartOpen, setCartOpen] = useState(false);
    const [wishlistOpen, setWishlistOpen] = useState(false);

    const openCategories = useCallback(() => {
        setCartOpen(false);
        setWishlistOpen(false);
        setCategoriesOpen(true);
    }, []);
    const closeCategories = useCallback(() => setCategoriesOpen(false), []);

    const openCart = useCallback(() => {
        setCategoriesOpen(false);
        setWishlistOpen(false);
        setCartOpen(true);
    }, []);
    const closeCart = useCallback(() => setCartOpen(false), []);

    const openWishlist = useCallback(() => {
        setCategoriesOpen(false);
        setCartOpen(false);
        setWishlistOpen(true);
    }, []);
    const closeWishlist = useCallback(() => setWishlistOpen(false), []);

    useEffect(() => {
        if (flash?.open_sheet === 'cart') {
            openCart();
        }
        if (flash?.open_sheet === 'wishlist') {
            openWishlist();
        }
    }, [flash?.open_sheet, openCart, openWishlist]);

    const shellClass = [
        'app-shell',
        categoriesOpen || cartOpen || wishlistOpen ? 'is-menu-open' : '',
        compactMain ? 'app-shell--compact' : '',
        hideHeaderOnMobile ? 'app-shell--no-header-mobile' : '',
    ]
        .filter(Boolean)
        .join(' ');

    return (
        <div className={shellClass}>
            <SeoHead seo={seo} />
            {!compactMain ? <AppPromoBanner /> : null}
            <Header
                current={current}
                onOpenCategories={openCategories}
                onOpenCart={openCart}
                onOpenWishlist={openWishlist}
            />
            <main className={`app-shell__main${compactMain ? ' app-shell__main--chat' : ''}`}>
                {children}
            </main>
            {!hideFooter ? <Footer /> : null}
            <BottomNav current={current} onOpenCategories={openCategories} />
            <CategoryDrawer open={categoriesOpen} onClose={closeCategories} />
            <CartSheet open={cartOpen} onClose={closeCart} />
            <WishlistSheet open={wishlistOpen} onClose={closeWishlist} />
        </div>
    );
}
