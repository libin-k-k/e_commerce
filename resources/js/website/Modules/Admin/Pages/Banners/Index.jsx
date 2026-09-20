import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ seo, banners = [] }) {
    const destroy = (id) => {
        if (!window.confirm('Delete this banner?')) {
            return;
        }

        router.delete(`/admin/banners/${id}`);
    };

    return (
        <AdminLayout title="Banners" current="banners">
            <Head title={seo?.title} />
            <div className="admin-toolbar">
                <p className="admin-toolbar__text">
                    Manage Hero, Middle, and Footer banners for the storefront.
                </p>
                <Link href="/admin/banners/create" className="btn btn--primary">
                    Create Banner
                </Link>
            </div>

            {banners.length === 0 ? (
                <p className="admin-empty">No banners yet. Create your first one.</p>
            ) : (
                <>
                    <div className="admin-card-list" aria-label="Banners">
                        {banners.map((banner) => (
                            <article key={banner.id} className="admin-card">
                                <div className="admin-banner-cell">
                                    {banner.web_image_url ? (
                                        <img
                                            className="admin-banner-cell__thumb"
                                            src={banner.web_image_url}
                                            alt=""
                                        />
                                    ) : (
                                        <span className="admin-banner-cell__thumb is-empty" />
                                    )}
                                    {banner.mobile_image_url ? (
                                        <img
                                            className="admin-banner-cell__thumb admin-banner-cell__thumb--mobile"
                                            src={banner.mobile_image_url}
                                            alt=""
                                        />
                                    ) : null}
                                    <div>
                                        <p className="admin-banner-cell__title">{banner.title}</p>
                                        <p className="admin-banner-cell__sub">{banner.subtitle}</p>
                                    </div>
                                </div>

                                <dl className="admin-card__meta">
                                    <div>
                                        <dt>Position</dt>
                                        <dd>{banner.position_label}</dd>
                                    </div>
                                    <div>
                                        <dt>Style</dt>
                                        <dd>{banner.style_label}</dd>
                                    </div>
                                    <div>
                                        <dt>Link</dt>
                                        <dd>{banner.navigation_link_label}</dd>
                                    </div>
                                    <div>
                                        <dt>Order</dt>
                                        <dd>{banner.sort_order}</dd>
                                    </div>
                                    <div>
                                        <dt>Status</dt>
                                        <dd>
                                            <span
                                                className={`account-badge account-badge--${banner.is_active ? 'success' : 'muted'}`}
                                            >
                                                {banner.is_active ? 'Active' : 'Inactive'}
                                            </span>
                                        </dd>
                                    </div>
                                </dl>

                                <div className="admin-card__actions">
                                    <Link
                                        href={`/admin/banners/${banner.id}/edit`}
                                        className="btn btn--ghost btn--compact"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        type="button"
                                        className="btn btn--ghost btn--compact is-danger"
                                        onClick={() => destroy(banner.id)}
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
                                    <th>Banner</th>
                                    <th>Position</th>
                                    <th>Style</th>
                                    <th>Link</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {banners.map((banner) => (
                                    <tr key={banner.id}>
                                        <td>
                                            <div className="admin-banner-cell">
                                                {banner.web_image_url ? (
                                                    <img
                                                        className="admin-banner-cell__thumb"
                                                        src={banner.web_image_url}
                                                        alt=""
                                                    />
                                                ) : (
                                                    <span className="admin-banner-cell__thumb is-empty" />
                                                )}
                                                {banner.mobile_image_url ? (
                                                    <img
                                                        className="admin-banner-cell__thumb admin-banner-cell__thumb--mobile"
                                                        src={banner.mobile_image_url}
                                                        alt=""
                                                    />
                                                ) : null}
                                                <div>
                                                    <p className="admin-banner-cell__title">{banner.title}</p>
                                                    <p className="admin-banner-cell__sub">{banner.subtitle}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{banner.position_label}</td>
                                        <td>{banner.style_label}</td>
                                        <td>{banner.navigation_link_label}</td>
                                        <td>{banner.sort_order}</td>
                                        <td>
                                            <span
                                                className={`account-badge account-badge--${banner.is_active ? 'success' : 'muted'}`}
                                            >
                                                {banner.is_active ? 'Active' : 'Inactive'}
                                            </span>
                                        </td>
                                        <td>
                                            <div className="admin-table__actions">
                                                <Link
                                                    href={`/admin/banners/${banner.id}/edit`}
                                                    className="admin-table__link"
                                                >
                                                    Edit
                                                </Link>
                                                <button
                                                    type="button"
                                                    className="admin-table__link is-danger"
                                                    onClick={() => destroy(banner.id)}
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
