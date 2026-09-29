import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import AppPromoBanner from '../Components/AppPromoBanner';
import BottomNav from '../Components/BottomNav';
import CartSheet from '../Components/CartSheet';
import CategoryBar from '../Components/CategoryBar';
import CategoryDrawer from '../Components/CategoryDrawer';
import Footer from '../Components/Footer';
import Header from '../Components/Header';
import SearchScreen from '../Components/SearchScreen';
import SeoHead from '../Components/SeoHead';
import WishlistSheet from '../Components/WishlistSheet';

const StorefrontActionsContext = createContext({
    openCategories: () => {},
    openCart: () => {},
    openWishlist: () => {},
    openSearch: () => {},
});

export function useStorefrontActions() {
    return useContext(StorefrontActionsContext);
}

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
    const [searchOpen, setSearchOpen] = useState(false);

    const openCategories = useCallback(() => {
        setCartOpen(false);
        setWishlistOpen(false);
        setSearchOpen(false);
        setCategoriesOpen(true);
    }, []);
    const closeCategories = useCallback(() => setCategoriesOpen(false), []);

    const openCart = useCallback(() => {
        setCategoriesOpen(false);
        setWishlistOpen(false);
        setSearchOpen(false);
        setCartOpen(true);
    }, []);
    const closeCart = useCallback(() => setCartOpen(false), []);

    const openWishlist = useCallback(() => {
        setCategoriesOpen(false);
        setCartOpen(false);
        setSearchOpen(false);
        setWishlistOpen(true);
    }, []);
    const closeWishlist = useCallback(() => setWishlistOpen(false), []);

    const openSearch = useCallback(() => {
        setCategoriesOpen(false);
        setCartOpen(false);
        setWishlistOpen(false);
        setSearchOpen(true);
    }, []);
    const closeSearch = useCallback(() => setSearchOpen(false), []);

    useEffect(() => {
        if (current === 'cart') {
            return;
        }

        if (flash?.open_sheet === 'cart') {
            openCart();
        }
        if (flash?.open_sheet === 'wishlist') {
            openWishlist();
        }
    }, [current, flash?.open_sheet, openCart, openWishlist]);

    const actions = useMemo(
        () => ({ openCategories, openCart, openWishlist, openSearch }),
        [openCategories, openCart, openWishlist, openSearch],
    );

    const shellClass = [
        'app-shell',
        categoriesOpen || cartOpen || wishlistOpen || searchOpen ? 'is-menu-open' : '',
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
                onOpenSearch={openSearch}
            />
            {!compactMain ? <CategoryBar onOpenCategories={openCategories} /> : null}
            <main className={`app-shell__main${compactMain ? ' app-shell__main--chat' : ''}`}>
                <StorefrontActionsContext.Provider value={actions}>{children}</StorefrontActionsContext.Provider>
            </main>
            {!hideFooter ? <Footer /> : null}
            <BottomNav
                current={categoriesOpen ? 'categories' : current}
                onOpenCategories={openCategories}
            />
            <CategoryDrawer
                open={categoriesOpen}
                onClose={closeCategories}
                onOpenCart={openCart}
                onOpenWishlist={openWishlist}
            />
            <CartSheet open={cartOpen} onClose={closeCart} />
            <WishlistSheet open={wishlistOpen} onClose={closeWishlist} />
            <SearchScreen open={searchOpen} onClose={closeSearch} />
        </div>
    );
}
