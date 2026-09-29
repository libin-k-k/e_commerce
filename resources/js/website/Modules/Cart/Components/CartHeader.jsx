import { router, usePage } from '@inertiajs/react';
import { useEffect, useId, useRef, useState } from 'react';
import { useStorefrontActions } from '../../../Shared/Layouts/StorefrontLayout';
import { BackIcon, HeartIcon, MoreIcon } from './CartIcons';

export default function CartHeader({ count, allSelected, hasItems, onToggleAll, onClearCart }) {
    const { wishlistCount = 0 } = usePage().props;
    const { openWishlist } = useStorefrontActions();
    const menuId = useId();
    const menuRef = useRef(null);
    const [menuOpen, setMenuOpen] = useState(false);

    useEffect(() => {
        if (!menuOpen) {
            return undefined;
        }

        const onPointerDown = (event) => {
            if (!menuRef.current?.contains(event.target)) {
                setMenuOpen(false);
            }
        };
        const onKeyDown = (event) => {
            if (event.key === 'Escape') {
                setMenuOpen(false);
            }
        };

        document.addEventListener('pointerdown', onPointerDown);
        window.addEventListener('keydown', onKeyDown);

        return () => {
            document.removeEventListener('pointerdown', onPointerDown);
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [menuOpen]);

    const goBack = () => {
        if (window.history.length > 1) {
            window.history.back();
            return;
        }

        router.visit('/');
    };

    const runMenuAction = (action) => {
        setMenuOpen(false);
        action();
    };

    return (
        <header className="listing-header listing-header--cart">
            <div className="listing-header__bar">
                <button type="button" className="listing-header__icon" aria-label="Go back" onClick={goBack}>
                    <BackIcon />
                </button>
                <p className="listing-header__title cart-header__title">
                    My Cart <span>({count} {count === 1 ? 'item' : 'items'})</span>
                </p>
                <div className="listing-header__actions" ref={menuRef}>
                    <button type="button" className="listing-header__icon" aria-label="Wishlist" onClick={openWishlist}>
                        <HeartIcon size={21} />
                        {wishlistCount > 0 ? <span className="listing-header__badge">{wishlistCount}</span> : null}
                    </button>
                    <button
                        type="button"
                        className={`listing-header__icon${menuOpen ? ' is-active' : ''}`}
                        aria-label="More cart options"
                        aria-haspopup="menu"
                        aria-expanded={menuOpen}
                        aria-controls={menuId}
                        onClick={() => setMenuOpen((open) => !open)}
                    >
                        <MoreIcon />
                    </button>
                    {menuOpen ? (
                        <div id={menuId} className="cart-header__menu" role="menu">
                            {hasItems ? (
                                <>
                                    <button type="button" role="menuitem" onClick={() => runMenuAction(onToggleAll)}>
                                        {allSelected ? 'Deselect all items' : 'Select all items'}
                                    </button>
                                    <button
                                        type="button"
                                        role="menuitem"
                                        className="cart-header__menu-danger"
                                        onClick={() => runMenuAction(onClearCart)}
                                    >
                                        Clear cart
                                    </button>
                                </>
                            ) : null}
                            <button type="button" role="menuitem" onClick={() => runMenuAction(() => router.visit('/products'))}>
                                Continue shopping
                            </button>
                        </div>
                    ) : null}
                </div>
            </div>
        </header>
    );
}
