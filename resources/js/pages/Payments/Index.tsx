import { ImageRadioSelect } from "@/components/Select";
import { Head, useForm } from "@inertiajs/react";
import ecpay from '@images/payments/ecpay.png';
import {
    InputGroup,
    InputGroupAddon,
    InputGroupInput,
    InputGroupText,
    InputGroupTextarea,
} from "@/components/ui/input-group"
import { Button } from "@/components/ui/button";
import { useAuth } from "@/hooks/useAuth";

const channels = [
    { id: "1", src: ecpay, alt: "Ecpay", channel: 'ecpay' },
]



export default function PaymentsPage() {
    const { user, isAuthenticated } = useAuth()
    const { data, setData, post, processing, errors, reset } = useForm({
        name: user.name,
        channel: 'ecpay',
        amount: 0,
        message: '',
    });

    const submit = (e: React.SubmitEvent<HTMLFormElement>) => {
        e.preventDefault();
        post('/comments/donations', {
            onFinish: () => reset('message'),
        });
    };


    const remainTextLen = 255 - data.message.length + '/255'
    const channelSelected = channels.find(e => e.channel === data.channel)?.id ?? '1'

    const handleChannelChange = (e: React.ChangeEvent<HTMLSelectElement>) => {
        const targetChannel = channels.find(ch => ch.id === e.target.value)
        setData('channel', targetChannel?.channel ?? 'ecpay');
    };

    const handleAmountChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const reg = /[^0-9]*/g
        const value = e.target.value.replace(reg, '')
        setData('amount', parseInt(value));
    }


    return (<>
        <Head title="贊助" />
        <div className="mx-auto max-w-5xl space-y-8">
            <section className="space-y-4">
                <div className="flex flex-col items-center gap-5 py-12 text-center">
                    <ImageRadioSelect images={channels} selected={channelSelected} onSelected={handleChannelChange} />
                    贊助留言 (將於首頁顯示贊助內容)
                    <form onSubmit={submit} className="grid w-full max-w-sm gap-6">
                        <InputGroup>
                            <InputGroupAddon>
                                <InputGroupText>$</InputGroupText>
                            </InputGroupAddon>
                            <InputGroupInput placeholder="0" value={data.amount} onChange={handleAmountChange} />
                            <InputGroupAddon align="inline-end">
                                <InputGroupText>TWD</InputGroupText>
                            </InputGroupAddon>
                        </InputGroup>
                        <InputGroup>
                            <InputGroupInput placeholder="Enter your name" disabled={isAuthenticated} value={data.name} onChange={e => setData('name', e.target.value)} />
                        </InputGroup>
                        <InputGroup>
                            <InputGroupTextarea placeholder="Enter your message" maxLength={255} value={data.message} onChange={e => setData('message', e.target.value)} />
                            <InputGroupAddon align="block-end">
                                <InputGroupText className="text-xs text-muted-foreground">
                                    {remainTextLen}
                                </InputGroupText>
                            </InputGroupAddon>

                        </InputGroup>
                        {!!Object.keys(errors).length && (
                            <p className="mt-1 px-1 text-xs text-destructive">
                                {Object.values(errors)[0]}
                            </p>
                        )}
                        <Button type="submit" variant="secondary" disabled={!data.message.length || processing}>{processing ? '確認中' : '確認'}</Button>
                    </form>
                </div>
            </section>
        </div >
    </>)

}