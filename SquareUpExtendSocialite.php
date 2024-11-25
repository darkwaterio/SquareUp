<?php

namespace darkwaterio\SquareUp;

use SocialiteProviders\Manager\SocialiteWasCalled;

class StripeExtendSocialite
{
    public function handle(SocialiteWasCalled $socialiteWasCalled): void
    {
        $socialiteWasCalled->extendSocialite('squareup', Provider::class);
    }
}
