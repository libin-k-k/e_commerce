import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import CouponForm from '../../Sections/Coupons/CouponForm';

export default function Edit({ seo, coupon, options }) {
    return (
        <AdminLayout title="Edit Coupon" current="coupons">
            <Head title={seo?.title} />
            <CouponForm
                options={options}
                coupon={coupon}
                submitUrl={`/admin/coupons/${coupon.id}`}
                method="put"
                submitLabel="Save Coupon"
            />
        </AdminLayout>
    );
}
