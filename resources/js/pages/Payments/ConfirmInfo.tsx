import { Button } from "@/components/ui/button"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Separator } from "@base-ui/react"
import { Head } from "@inertiajs/react"



interface EcpayInfo {
    CheckMacValue: string
    ChoosePayment: string
    EncryptType: number
    MerchantID: string
    MerchantTradeDate: string
    MerchantTradeNo: string
    PaymentType: string
    ReturnURL: string
    PaymentInfoURL: string
    TotalAmount: number
    ItemName: string
    TradeDesc: string
}

interface ConfirmInfoPageProps {
    info: EcpayInfo,
    status: string
}

const ecpayUrl = import.meta.env.VITE_ECPAY_URL

export default function ConfirmInfoPage({ info, status }: ConfirmInfoPageProps) {

    const submit = () => {
        // 點擊「確認結帳」按鈕時呼叫：
        submitToEcpay(ecpayUrl, info);
    }
    return (<>
        <Head title="付款資訊" />
        <div className="mx-auto max-w-5xl space-y-8">
            <section className="space-y-4">
                <div className="flex flex-col items-center gap-5 py-12 text-center">
                    <Card className="w-full max-w-md">
                        <CardHeader className="flex flex-row justify-between">
                            <CardTitle>購買資訊</CardTitle>
                            <CardDescription>訂單編號 #{info.MerchantTradeNo}</CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            {/* 商品項目列表 */}
                            <div className="space-y-2 text-sm">
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        {info.ItemName} x 1
                                    </span>
                                    <span>NT$ {info.TotalAmount}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-muted-foreground">
                                        ({info.TradeDesc})
                                    </span>
                                </div>

                            </div>

                            <Separator />

                            {/* 總計 */}
                            <div className="flex justify-between font-medium">
                                <span>總計金額</span>
                                <span>NT$ {info.TotalAmount}</span>
                            </div>

                            <Separator />

                            {/* 付款資訊 */}
                            <div className="space-y-1 text-xs text-muted-foreground">
                                <div className="flex justify-between">
                                    <span>付款方式</span>
                                    <span>綠界科技 ECPay</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Button variant="secondary" onClick={submit} disabled={status === 'success'}>{
                        status === 'success' ? '已付款' : '確認並付款'}
                    </Button>
                </div>
            </section>
        </div>
    </>)

}

/**
 * 動態建立隱藏表單並自動跳轉至綠界
 * @param {string} actionUrl 綠界結帳網址 (測試: https://ecpay.com.tw)
 * @param {Object} params 後端回傳的 JSON 參數物件
 */
function submitToEcpay(actionUrl: string, params: EcpayInfo) {
    // 1. 建立 form 元素
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = actionUrl;
    form.style.display = 'none'; // 隱藏表單

    // 2. 遍歷 JSON 物件，將每個欄位轉成 <input type="hidden">
    Object.entries(params).forEach(([key, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = String(value); // 確保 number (如 TotalAmount, EncryptType) 轉為字串
        form.appendChild(input);
    });

    // 3. 必須將 form 掛載到 document.body 才能觸發 submit
    document.body.appendChild(form);
    form.submit();
}