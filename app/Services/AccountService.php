<?php

namespace App\Services\Salesforce;

use TradeTree\Role;
use TradeTree\Company;
use TradeTree\Models\Salesforce\Account;

class AccountService
{
    public static function storeCompany(Account $account)
    {
        $company = Company::updateOrCreate([
            'sf_account_id' => $account->id,
        ], [
            'legal_name' => $account->name,
            'phone_number' => $account->phone,
            'alias' => $account->name,
        ]);

        if (!is_null($role = self::parseRole($account))) {
            $company->roles()->sync([$role->id]);
        }

        return $company;
    }

    public static function parseRole(Account $account)
    {
        switch ($account->business__type__c) {
            case Account::BUSINESS_TYPE_PRODUCER:
            case Account::BUSINESS_TYPE_LARGE_PRODUCER:
                $type = 'seller';
                break;
            case Account::BUSINESS_TYPE_BUYER:
            case Account::BUSINESS_TYPE_LARGE_BUYER:
                $type = 'buyer';
                break;
            case Account::BUSINESS_TYPE_AGENT:
                $type = 'agent';
                break;
            case Account::BUSINESS_TYPE_OTHER:
            default:
                $type = 'other';
        }

        return Role::where('type', $type)->first();
    }
}