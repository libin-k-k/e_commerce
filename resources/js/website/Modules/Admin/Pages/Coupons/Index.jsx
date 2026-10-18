import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ seo, coupons = [] }) {
    const destroy = (id) => {
        if (!window.confirm('Delete this coupon?')) {
            return;
        }

        router.delete(`/admin/coupons/${id}`);
    };

    return (
        <AdminLayout title="Coupons" current="coupons">
            <Head title={seo?.title} />
            <div className="admin-toolbar">
                <p className="admin-toolbar__text">
                    Create percentage or fixed coupons with validity, order limits, product rules, and
                    usage caps.
                </p>
                <Link href="/admin/coupons/create" className="btn btn--primary">
                    Create Coupon
                </Link>
            </div>

            {coupons.length === 0 ? (
                <p className="admin-empty">No coupons yet. Create your first coupon.</p>
            ) : (
                <div className="admin-table-wrap">
                    <table className="admin-table">
                        <thead>
                            <tr>
                                <th scope="col">Code</th>
                                <th scope="col">Type</th>
                                <th scope="col">Value</th>
                                <th scope="col">Validity</th>
                                <th scope="col">Usage</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            {coupons.map((coupon) => (
                                <tr key={coupon.id}>
                                    <td>
                                        <strong>{coupon.code}</strong>
                                        {coupon.name ? <div className="admin-table__muted">{coupon.name}</div> : null}
                                        {coupon.first_order_only ? (
                                            <div className="admin-table__muted">First order only</div>
                                        ) : null}
                                    </td>
                                    <td>{coupon.type_label}</td>
                                    <td>
                                        {coupon.type === 'percent'
                                            ? `${coupon.value}% (max ₹${coupon.max_discount_amount ?? 0})`
                                            : `₹${coupon.value}`}
                                    </td>
                                    <td>
                                        {coupon.has_validity
                                            ? `${coupon.starts_at ?? '—'} → ${coupon.ends_at ?? '—'}`
                                            : 'No expiry'}
                                    </td>
                                    <td>
                                        {coupon.redemptions_count}
                                        {coupon.total_usage_limit ? ` / ${coupon.total_usage_limit}` : ''}
                                        {coupon.per_user_usage_limit
                                            ? ` · ${coupon.per_user_usage_limit}/user`
                                            : ''}
                                    </td>
                                    <td>
                                        <span
                                            className={`account-badge account-badge--${coupon.is_active ? 'success' : 'muted'}`}
                                        >
                                            {coupon.is_active ? 'Active' : 'Inactive'}
                                        </span>
                                    </td>
                                    <td>
                                        <div className="admin-card__actions">
                                            <Link
                                                href={`/admin/coupons/${coupon.id}/edit`}
                                                className="admin-table__link"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                type="button"
                                                className="admin-table__link is-danger"
                                                onClick={() => destroy(coupon.id)}
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
            )}
        </AdminLayout>
    );
}
