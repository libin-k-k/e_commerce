import { Head } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import CouponForm from '../../Sections/Coupons/CouponForm';

export default function Create({ seo, options }) {
    return (
        <AdminLayout title="Create Coupon" current="coupons">
            <Head title={seo?.title} />
            <CouponForm
                options={options}
                submitUrl="/admin/coupons"
                method="post"
                submitLabel="Create Coupon"
            />
        </AdminLayout>
    );
}
