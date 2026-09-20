import AccountShell from '../Components/AccountShell';
import AccountSubpageHeader from '../Components/AccountSubpageHeader';
import PoliciesSection from '../Sections/PoliciesSection';

export default function Policies({ seo, policies = [], menu = [], profile, active = 'policies' }) {
    return (
        <AccountShell seo={seo} menu={menu} profile={profile} active={active}>
            <AccountSubpageHeader title="Terms & policies" />
            <PoliciesSection policies={policies} />
        </AccountShell>
    );
}
