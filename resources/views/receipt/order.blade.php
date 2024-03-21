<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8" />
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

		<title>Receipt</title>

		<style>
            @media print {
                .invoice-box {
                    max-width: unset;
                    box-shadow: none;
                    border: 0px;
                }
            }
			.invoice-box {
				max-width: 800px;
				margin: auto;
				padding: 30px;
				border: 1px solid #eee;
				box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
				font-size: 16px;
				line-height: 24px;
				font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
				color: #555;
			}

			.invoice-box table {
				width: 100%;
				line-height: inherit;
				text-align: left;
			}

			.invoice-box table td {
				padding: 5px;
				vertical-align: top;
			}

			.invoice-box table tr td:nth-child(2) {
				text-align: right;
			}

			.invoice-box table tr.top table td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.top table td.title {
				font-size: 45px;
				line-height: 45px;
				color: #333;
			}

			.invoice-box table tr.information table td {
				padding-bottom: 40px;
			}

			.invoice-box table tr.heading td {
				background: #eee;
				border-bottom: 1px solid #ddd;
				font-weight: bold;
			}

			.invoice-box table tr.details td {
				padding-bottom: 20px;
			}

			.invoice-box table tr.item td {
				border-bottom: 1px solid #eee;
			}

			.invoice-box table tr.item.last td {
				border-bottom: none;
			}

			.invoice-box table tr.total td:nth-child(2) {
				border-top: 2px solid #eee;
				font-weight: bold;
			}

			@media only screen and (max-width: 600px) {
				.invoice-box table tr.top table td {
					width: 100%;
					display: block;
					text-align: center;
				}

				.invoice-box table tr.information table td {
					width: 100%;
					display: block;
					text-align: center;
				}
			}

			/** RTL **/
			.invoice-box.rtl {
				direction: rtl;
				font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
			}

			.invoice-box.rtl table {
				text-align: right;
			}

			.invoice-box.rtl table tr td:nth-child(2) {
				text-align: left;
			}
		</style>
	</head>

	<body>
		<div class="invoice-box">
			<table cellpadding="0" cellspacing="0">
				<tr class="top">
					<td colspan="4">
						<table>
							<tr>
								<td class="title">
									<img
										src="https://assets.pi55xx.com/larashop/assets/larashop-logo.PNG"
										style="width: 100%; max-width: 100px"
									/>
								</td>

								<td>
									Receipt #: {{$order->id}}<br />
									Paid at: {{$order->paid_at}}<br />
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="information">
					<td colspan="4">
						<table>
							<tr>
								<td>
									Larapay, Inc.<br />
									12345 Sunny Road<br />
									Sunnyville, CA 12345
								</td>

								<td>
									{{$order->user->name}}<br />
									{{$order->user->email}}
								</td>
							</tr>
						</table>
					</td>
				</tr>

				<tr class="heading">
					<td colspan="4">Payment Method</td>
				</tr>

				<tr class="details" style="text-transform: capitalize;">
					<td colspan="2">{{ $order->payment->type }}</td>
				</tr>

				<tr class="heading">
					<td>Item</td>

					<td style="text-align: right;">Price</td>

					<td style="text-align: right;">Quantity</td>

                    <td style="text-align: right;">Total</td>
				</tr>
                @foreach ($order->items as $item)
                <tr class="item">
                    <td>{{$item->name}}</td>
                    <td style="text-align: right;">${{$item->pivot->price}}</td>
                    <td style="text-align: right;">${{$item->pivot->quantity}}</td>
                    <td style="text-align: right;">
                        {{number_format($item->pivot->quantity * $item->pivot->price, 2)}}
                    </td>
                </tr>
                @endforeach

				<tr class="total">
					<td colspan="2"></td>
                    <td style="text-align: right;">Total</td>
					<td style="text-align: right;">${{number_format($order->amount, 2)}}</td>
				</tr>
			</table>
		</div>
	</body>
</html>
