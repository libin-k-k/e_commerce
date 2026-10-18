import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import OfferPageForm from '../../Sections/Offers/OfferPageForm';

function statusLabel(offer) {
    if (offer.is_live) {
        return 'Live';
    }

    if (offer.is_active) {
        return 'Scheduled';
    }

    return 'Off';
}

export default function Index({ seo, offers = [], pageSetting = null, themes = [], offerBanners = [] }) {
    const destroy = (id) => {
        if (!window.confirm('Delete this offer?')) {
            return;
        }

        router.delete(`/admin/offers/${id}`);
    };

    return (
        <AdminLayout title="Offers" current="offers">
            <Head title={seo?.title} />
            <div className="admin-toolbar">
                <p className="admin-toolbar__text">
                    Products on the offers page. The home recommended section stays category products.
                </p>
                <Link href="/admin/offers/create" className="btn btn--primary">
                    Create offer
                </Link>
            </div>

            <section className="admin-panel offer-admin-panel">
                <h2 className="admin-panel__title">Offer page</h2>
                <p className="admin-panel__text">
                    Rename the Offer Zone, turn the banner countdown on, and switch the season theme. View all on
                    that section still opens the offers page.
                </p>
                <OfferPageForm pageSetting={pageSetting} themes={themes} />
            </section>

            <section className="admin-panel offer-admin-panel">
                <div className="admin-toolbar">
                    <div>
                        <h2 className="admin-panel__title">Offer banners</h2>
                        <p className="admin-panel__text">Images at the top of the offers page. GIF uploads stay animated.</p>
                    </div>
                    <Link href="/admin/banners/create?position=offer_zone" className="btn btn--primary">
                        Add banner
                    </Link>
                </div>
                {offerBanners.length === 0 ? (
                    <p className="admin-empty">No offer banners yet.</p>
                ) : (
                    <div className="admin-card-list is-always" aria-label="Offer banners">
                        {offerBanners.map((banner) => (
                            <article key={banner.id} className="admin-card">
                                <div className="admin-banner-cell">
                                    {banner.web_image_url ? (
                                        <img className="admin-banner-cell__thumb" src={banner.web_image_url} alt="" />
                                    ) : (
                                        <span className="admin-banner-cell__thumb is-empty" />
                                    )}
                                    <div>
                                        <p className="admin-banner-cell__title">{banner.title}</p>
                                        <p className="admin-banner-cell__sub">{banner.subtitle}</p>
                                    </div>
                                </div>
                                <div className="admin-card__actions">
                                    <Link href={`/admin/banners/${banner.id}/edit`} className="btn btn--ghost btn--compact">
                                        Edit banner
                                    </Link>
                                </div>
                            </article>
                        ))}
                    </div>
                )}
            </section>

            {offers.length === 0 ? (
                <p className="admin-empty">No offers yet. Create a four-product deal card.</p>
            ) : (
                <>
                    <div className="admin-card-list" aria-label="Offers">
                        {offers.map((offer) => (
                            <article key={offer.id} className="admin-card">
                                <div>
                                    <p className="admin-banner-cell__title">{offer.title}</p>
                                    <p className="admin-banner-cell__sub">
                                        {(offer.products ?? []).map((product) => product.name).join(', ') ||
                                            'No products'}
                                    </p>
                                </div>
                                <dl className="admin-card__meta">
                                    <div>
                                        <dt>Starts</dt>
                                        <dd>{offer.starts_at || 'Open'}</dd>
                                    </div>
                                    <div>
                                        <dt>Ends</dt>
                                        <dd>{offer.ends_at || 'Open'}</dd>
                                    </div>
                                    <div>
                                        <dt>Countdown</dt>
                                        <dd>{offer.countdown_enabled ? 'On' : 'Off'}</dd>
                                    </div>
                                    <div>
                                        <dt>Status</dt>
                                        <dd>{statusLabel(offer)}</dd>
                                    </div>
                                </dl>
                                <div className="admin-card__actions">
                                    <Link href={`/admin/offers/${offer.id}/edit`} className="btn btn--ghost btn--compact">
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        className="btn btn--ghost btn--compact is-danger"
                                        onClick={() => destroy(offer.id)}
                                    >
                                        Delete
                                    </button>
                                </div>
                            </article>
                        ))}
                    </div>

                    <div className="admin-table-wrap">
                        <table className="admin-table">
                            <thead>
                                <tr>
                                    <th scope="col">Offer</th>
                                    <th scope="col">Starts</th>
                                    <th scope="col">Ends</th>
                                    <th scope="col">Countdown</th>
                                    <th scope="col">Status</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {offers.map((offer) => (
                                    <tr key={offer.id}>
                                        <td>
                                            <p className="admin-banner-cell__title">{offer.title}</p>
                                            <p className="admin-banner-cell__sub">
                                                {(offer.products ?? []).map((product) => product.name).join(', ') ||
                                                    'No products'}
                                            </p>
                                        </td>
                                        <td>{offer.starts_at || 'Open'}</td>
                                        <td>{offer.ends_at || 'Open'}</td>
                                        <td>{offer.countdown_enabled ? 'On' : 'Off'}</td>
                                        <td>
                                            <span
                                                className={`account-badge account-badge--${offer.is_active ? 'success' : 'muted'}`}
                                            >
                                                {statusLabel(offer)}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="admin-table__actions">
                                                <Link
                                                    href={`/admin/offers/${offer.id}/edit`}
                                                    className="admin-table__link"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    className="admin-table__link is-danger"
                                                    onClick={() => destroy(offer.id)}
                                                >
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </>
            )}
        </AdminLayout>
    );
}
