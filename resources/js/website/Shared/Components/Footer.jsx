import { Link, usePage } from '@inertiajs/react';

export default function Footer() {
    const { appName } = usePage().props;
    const year = new Date().getFullYear();

    return (
        <footer className="site-footer">
            <div className="site-footer__inner">
                <div>
                    <p className="site-footer__brand">{appName}</p>
                    <p className="site-footer__text">
                        Shop curated products with a fast, mobile-first checkout experience.
                    </p>
                </div>

                <div className="site-footer__links">
                    <div>
                        <p className="site-footer__heading">Shop</p>
                        <ul className="site-footer__list">
                            <li>
                                <Link href="/products">All products</Link>
                            </li>
                            <li>
                                <Link href="/products">New arrivals</Link>
                            </li>
                            <li>
                                <Link href="/products">Best sellers</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p className="site-footer__heading">Account</p>
                        <ul className="site-footer__list">
                            <li>
                                <Link href="/account">My account</Link>
                            </li>
                            <li>
                                <Link href="/account/orders">Order history</Link>
                            </li>
                            <li>
                                <Link href="/account/addresses">Addresses</Link>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <p className="site-footer__heading">Help</p>
                        <ul className="site-footer__list">
                            <li>
                                <Link href="/help">Support chat</Link>
                            </li>
                            <li>
                                <Link href="/account/faq">FAQ</Link>
                            </li>
                            <li>
                                <Link href="/account/policies">Terms & policies</Link>
                            </li>
                        </ul>
                    </div>
                </div>

                <p className="site-footer__copy">
                    © {year} {appName}. All rights reserved.
                </p>
            </div>
        </footer>
    );
}
