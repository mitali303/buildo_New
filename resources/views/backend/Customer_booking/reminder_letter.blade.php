<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">

<style>
@media print {.dontPrint{display:none;}}
.rcorners2{width:750px;}
table{border-collapse:collapse;}
td{border:1px solid black;font-size:14px;padding:6px;}

tbody{
    border: 1px solid black;
}
td{
    border:none
}
</style>

</head>
<body>

<center>
<button onclick="window.print()" class="dontPrint"><b>Print</b></button>
</center><br>

<center>
<div class="rcorners2">
<table width="100%">

<tr>
<td align="center"><b>स्मरण पत्र</b></td>
</tr>

<tr><td><b>प्रति,</b></td></tr>

<tr><td><b>{{ $booking->CutomerName }}</b></td></tr>

<tr><td><b>{{ $booking->Address }}</b></td></tr>

<tr>
<td><b>TAL. {{ $booking->village }} {{ $booking->district }}</b></td>
</tr>

<tr><td>महोदय,</td></tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
आपण आमच्या मांजे {{ $scheme->Location }} येथील सर्वे नंबर
{{ $scheme->Address }} मधील क्षेत्र {{ $scheme->Area }} चौ. मी. व्या मिळकतीवर
</td>
</tr>

<tr>
<td>आम्ही 
"{{ $scheme->Name }}" या नावाने उभारले असलेले रहिवासी वाणिज्य गृह प्रकल्पातील
{{ $flat->Floor }} मजल्यावरील प्लॅट नं {{ $flat->FlatNo }} चे बुकिंग/ साठेखत करारनामा आपण केलेला आहे.
</td>
</tr>

<tr>
<td>
सदर फ्लॅटची संपूर्ण मोबदला रक्कम सर्व खर्चासह रूपये {{ $booking->TotalFlatAmt }} /- हि {{ $scheme->Name }} यांना देण्याचे मान्य व कबूल केले असून आपणाकडून बँक लोन सोडून उर्वरित येणे रक्कमेचा तपशील खालील प्रमाणे
</td>
</tr>

<tr>
<td style="text-align: center;">
<b>मार्जिन मनी (स्वतःचे योगदान)</b> = ₹ {{ $selfmoney }} /-
</td>
</tr>

<tr>
<td style="text-align: center;">
<b>आज अखेर जमा रक्कम</b> = ₹ {{ $today_paid_amt }} /-
</td>
</tr>

<tr>
<td style="text-align: center;">
<b>उर्वरित येणे रक्कम</b> = ₹ {{ $selfmoney - $today_paid_amt }} /-
</td>
</tr>

<tr>
<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
आज अखेरची मार्जिन मनी  (स्वतःचे योगदान ) रक्कम रुपये {{ $selfmoney - $today_paid_amt }} /- येणे आहे. आपले "{{ $scheme->Name }}" प्रकल्पाचे बांधकाम जवळ जवळ {{ $request->workcompletion }} % इतके पूर्ण झाले आहे.
</td>
</tr>


<tr>
<td>
असून उर्वरित बांधकाम साधारणतः {{ $fdate->format('F Y') }} ते {{ $tdate->format('F Y') }} पर्यंत पूर्ण होण्याची शक्यता आहे तरी आपली उर्वरित मार्जिन मनी ( स्वतःचे योगदान ) रक्कम तोपर्यंत आदा करावी हि विनंती.
</td>
</tr>



<tr>
<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
 सदरील स्मरणपत्र हे आपणास पोस्ट द्वारे व वॉट्सॲप द्वारे पोच करीत आहोत याची नोंद घ्यावी.
</td>
</tr>

<tr>
<td><b>आपला विश्वासू,</b></td>
</tr>
<tr>
<td></td>
</tr>
<tr>
<td></td>
</tr>
<tr>
<td></td>
</tr>

<tr >
<td><b>{{ $scheme->Name }}</b></td>
</tr>

<tr>
<td><b>{{ $scheme->Address }}</b></td>
</tr>

</table>
</div>
</center>

</body>
</html>
