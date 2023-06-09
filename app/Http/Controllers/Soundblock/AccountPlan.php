<?php

namespace App\Http\Controllers\Soundblock;

use Auth;
use Exception;
use App\Models\Users\User;
use Illuminate\Http\Response;
use App\Contracts\Payment\Payment;
use App\Http\Controllers\Controller;
use App\Jobs\Soundblock\Ledger\ServiceLedger;
use App\Http\Transformers\Account as AccountTransformer;
use App\Http\Requests\Common\Account\{ChargePastDueAccountPlan, UpdatePlan, CreatePlan};
use App\Contracts\Soundblock\Accounting\Accounting as AccountingService;
use App\Repositories\Soundblock\Data\PlansTypes as PlansTypesRepository;
use App\Services\{
    Common\AccountPlan as AccountPlanService,
    Common\Common,
    Soundblock\Ledger\ServiceLedger as ServiceLedgerService
};

/**
 * @group Soundblock
 *
 * Soundblock routes
 */
class AccountPlan extends Controller {
    /** @var AccountPlanService */
    protected AccountPlanService $planService;
    /** @var Common */
    private Common $commonService;
    /** @var Payment */
    private Payment $payment;
    /** @var PlansTypesRepository */
    private PlansTypesRepository $plansTypesRepo;
    /** @var AccountingService */
    private AccountingService $accountingService;

    /**
     * @param Common $commonService
     * @param AccountPlanService $planService
     * @param Payment $payment
     * @param PlansTypesRepository $plansTypesRepo
     * @param AccountingService $accountingService
     */
    public function __construct(Common $commonService, AccountPlanService $planService, Payment $payment,
                                PlansTypesRepository $plansTypesRepo, AccountingService $accountingService) {
        $this->planService = $planService;
        $this->commonService = $commonService;
        $this->payment = $payment;
        $this->plansTypesRepo = $plansTypesRepo;
        $this->accountingService = $accountingService;
    }

    public function getActualPlansTypes(){
        $objPlanTypes = $this->planService->getActualPlansTypes();

        return ($this->apiReply($objPlanTypes, "", Response::HTTP_OK));
    }

    public function getDataForPastDueAccount(string $account_plan){
        /** @var User $objUser */
        $objUser = Auth::user();
        $objAccount = $this->commonService->find($account_plan);

        if ($objUser->user_uuid != $objAccount->user_uuid) {
            throw new \Exception("User hasn't own this account.", 400);
        }

        if ($objAccount->flag_status != "past due accounts") {
            throw new \Exception("Accounts are not past due.", 400);
        }

        $arrData = $this->planService->getAccountsPastDue($objAccount);

        return ($this->apiReply($arrData, "", Response::HTTP_OK));
    }

    /**
     * @param CreatePlan $objRequest
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|Response|object
     * @throws Exception
     */
    public function store(CreatePlan $objRequest) {
        /** @var User $objUser*/
        $objUser = Auth::user();
        $objPaymentMethod = null;
        $objPlanType = $this->plansTypesRepo->find($objRequest->input("type"));

        if ($objRequest->has("payment_id") && is_string($objRequest->input("payment_id"))) {
            $this->payment->getOrCreateCustomer($objUser);
            $objPaymentMethod = $this->payment->addPaymentMethod($objUser, $objRequest->input("payment_id"));
        }

        if ($objPlanType->plan_level !== 1 && is_null($objPaymentMethod) && is_null($objUser->defaultPaymentMethod())) {
            throw new \Exception("User Doesn't Have Any Payment Methods.");
        }

        [$objAccount, $objAccountPlan] = $this->commonService->createNew($objRequest->account_name, $objPlanType, $objUser);

        dispatch(new ServiceLedger(
            $objAccount,
            ServiceLedgerService::CREATE_EVENT,
            [
                "remote_addr" => request()->getClientIp(),
                "remote_host" => gethostbyaddr(request()->getClientIp()),
                "remote_agent" => request()->server("HTTP_USER_AGENT")
            ]
        ))->onQueue("ledger");

        return ($this->apiReply($objAccount->load("plans.planType"), "Account Plan Created Successfully.", Response::HTTP_OK));
    }

    /**
     * @param string|null $account_plan
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function cancel(?string $account_plan = null) {
        /** @var User $objUser */
        $objUser = \Auth::user();

        if ($account_plan) {
            $objAccount = $this->commonService->find($account_plan);

            if ($objUser->user_id !== $objAccount->user_id) {
                return $this->apiReject(null, "Only Account Owner Can Modify Contract Info", Response::HTTP_FORBIDDEN);
            }

            $objAccount = $this->planService->cancelNew($objAccount);

            return $this->item($objAccount, new AccountTransformer(["plans"]));
        }

        $objAccount = $this->planService->cancel($objUser);

        return $this->item($objAccount, new AccountTransformer(["plans"]));
    }

    /**
     * @param UpdatePlan $objRequest
     * @param string $account_plan
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function update(UpdatePlan $objRequest, string $account_plan) {
        /** @var User $objUser */
        $objUser = Auth::user();
        $objAccount = $this->commonService->find($account_plan);

        if ($objUser->user_uuid != $objAccount->user_uuid) {
            throw new \Exception("User hasn't own this account.", 400);
        }

        $objOldPlan = $this->planService->getActivePlan($objAccount);
        $objPlanType = $this->plansTypesRepo->find($objRequest->input("type"));

        if (isset($objOldPlan) && $objOldPlan->planType->plan_level > $objPlanType->plan_level) {
            throw new \Exception("You Can Not Downgrade Your Plan.");
        }

        if ($objRequest->has("payment_id") && is_string($objRequest->input("payment_id"))) {
            $this->payment->getOrCreateCustomer($objUser);
            $objMethod = $this->payment->addPaymentMethod($objUser, $objRequest->input("payment_id"));
        } else {
            $objMethod = $objUser->defaultPaymentMethod();
        }

        if (is_null($objMethod)){
            throw new \Exception("User doesn't have payment method.");
        }

        $objAccount = $this->planService->update($objAccount, $objPlanType, $objOldPlan);

        return $this->item($objAccount, new AccountTransformer(["plans"], true));
    }

    public function chargePastDueAccountPlan(ChargePastDueAccountPlan $objRequest, string $accountUuid){
        /** @var User $objUser */
        $objUser = Auth::user();
        $objAccount = $this->commonService->find($accountUuid);

        if ($objUser->user_uuid != $objAccount->user_uuid) {
            throw new \Exception("User hasn't own this account.", 400);
        }

        if ($objAccount->flag_status != "past due accounts") {
            throw new \Exception("Accounts are not past due.", 400);
        }

        $this->payment->getOrCreateCustomer($objUser);
        $objPaymentMethod = $this->payment->addPaymentMethod($objUser, $objRequest->input("payment_id"));
        [$boolResult, $objAccount] = $this->planService->chargePastDue($objAccount, $objPaymentMethod);

        if ($boolResult) {
            return ($this->apiReply($objAccount, "Account charged successfully.", Response::HTTP_OK));
        }

        return ($this->apiReject($objAccount, "", Response::HTTP_BAD_REQUEST));
    }
}
