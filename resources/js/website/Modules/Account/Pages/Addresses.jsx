import AccountShell from '../Components/AccountShell';
import AccountSubpageHeader from '../Components/AccountSubpageHeader';
import AddressesSection from '../Sections/AddressesSection';

export default function Addresses({
    seo,
    addresses = [],
    addressLabels = ['Home', 'Work', 'Other'],
    menu = [],
    profile,
    active = 'addresses',
}) {
    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountSubpageHeader title="Addresses" />
            <AddressesSection addresses={addresses} addressLabels={addressLabels} />
        </AccountShell>
    );
}
