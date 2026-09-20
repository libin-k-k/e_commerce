import AccountShell from '../Components/AccountShell';
import AccountSubpageHeader from '../Components/AccountSubpageHeader';
import OrdersSection from '../Sections/OrdersSection';

export default function Orders({ seo, orders = [], menu = [], profile, active = 'orders' }) {
    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountSubpageHeader title="Order history" />
            <OrdersSection orders={orders} />
        </AccountShell>
    );
}
