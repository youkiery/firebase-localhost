<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title> Trả kết quả xét nghiệm </title>
  <style>
    * {
      font-size: 12pt;
      font-family: "Tahoma", sans-serif;
    }
    .table td {
      padding: 4px;
    }
    .table-result td {
      height: 14pt;
    }
    .red {
      color: red;
      background: none;
    }
    .green {
      color: green;
    }
    .big {
      font-size: 1.3em;
    }
    .underline {
      border-bottom: 1px dashed black;
    }

    @media print{
      @page {
        size: A4 portrait;
        margin: 0.5in;
      }
      .pagebreak {
        clear: both;
        page-break-after: always;
      }
    }

  </style>
</head>
<body>
  <table border="1" style="border-collapse: collapse; width: 100%;">
    <tr style="height: 80px">
      <td style="width: 80px; padding: 1px;">
        <img style="width: 78px; height: 78px" src="data:image/jpeg;base64,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" alt="">
      </td>
      <td style="text-align: center;"> 
        <div> <b class="red big"> THANHXUANPET HOSPITAL </b> </div>  
        <div> <b class="green"> BỘ PHẬN XÉT NGHIỆM </b> </div>  
        <div> <b> Địa chỉ: </b> 12, 14 Lê Đại Hành, TP. Buôn Ma Thuột, Đắk Lắk </div>
        <div> <b> Điện thoại: </b> 02626 290609 – Website: thanhxuanpet.com </div>  
      </td>
      <td style="text-align: center; vertical-align:middle; width: 120px;">  
        <div> Biểu mẫu số 1 </div>
        <div> Số kiểm tra: 21 </div>
      </td>
    </tr>
  </table>
  <div style="margin: 15px 0px; text-align: center;">
    <div>
      <b> 
        PHIẾU TRẢ LỜI KẾT QUẢ XÉT NGHIỆM SINH HÓA 
        <div class="green">
          (Biochemical Test Result)
        </div>
      </b>
    </div>
  </div>

  <table class="table" border="1" style="border-collapse: collapse; width: 100%;">
    <tr>
      <td style="border-left: none;border-right: none; width: 50%;"> Tên chủ nuôi/Host name: <b> {customer} </b> </td>
      <td style="border-left: none;border-right: none; width: 10%;"></td>
      <td style="width: 40%;"> Sample ID: <b>{sampleid}</b> </td>
    </tr>
    <tr>
      <td colspan="3"> Địa chỉ/Address: <b>{address}</b> </td>
    </tr>
    <tr>
      <td style="border-left: none;border-right: none;"> Tên thú cưng/Pet name: <b>{name}</b> </td>
      <td style="border-left: none;border-right: none;"></td>
      <td> Trọng lượng/Weight: <b>{weight}kg</b> </td>
    </tr>
    <tr>
      <td> Tuổi/Age: <b>{age} </b> </td>
      <td colspan="2"> Giới tính: <b>{gender}</b> </td>
    </tr>
    <tr> 
      <td> Loại động vật/Pet type: <b> {type} </b> </td>
      <td colspan="2"> Mẫu xét nghiệm/Sample ID: <b>{sampleid}</b> </td>
    </tr>
    <tr>
      <td> Số Serial/Serial Number: <b>{serial}</b> </td>
      <td colspan="2"> Loại mẫu/Sample Type: <b>{sampletype}</b> </td>
    </tr>
    <tr> 
      <td colspan="3"> Số lượng mẫu/Number of samples: <b>{samplenumber} mẫu</b> </td>  
    </tr>
    <tr>
      <td> Ký hiệu mẫu/Symbol test: <b>{samplesymbol}</b> </td>
      <td colspan="2"> Tình trạng mẫu: <b>{samplestatus}</b> </td>
    </tr>
    <tr>
      <td colspan="3"> Bác sĩ thực hiện kiểm tra: <b>{doctor}</b> </td>
    </tr>
    <tr>
      <td colspan="3"> Ngày kiểm tra/Testing day: <b>{time}</b> </td>
    </tr>
  </table>

  <table border="1" class="table-result" style="margin-top: 10px; width: 100%; border-collapse: collapse; border: 1px solid white;">
    <tr class="underline">
      <td colspan="5"> <b>Kết quả/Result:</b> </td>
    </tr>
    <tr class="underline">
      <td> <b> Cheminstry </b> </td>
      <td> <b> Kết quả/Result </b> </td>
      <td> <b> Đơn vị tính/Unit </b> </td>
      <td> <b> Flag </b> </td>
      <td> <b> Ref Range </b> </td>
    </tr>
    <tr class="underline">
      <td> {target1} </td>
      <td> <b><span>{res1}</span> <span class="red">{ret1}</span> </b> </td>
      <td> {unit1} </td>
      <td> <span class="red"> {flag1} </span> </td>
      <td> {range1} </td>
    </tr>
    <tr class="underline">
      <td> {target2} </td>
      <td> <b><span>{res2}</span> <span class="red">{ret2}</span> </b> </td>
      <td> {unit2} </td>
      <td> <span class="red"> {flag2} </span> </td>
      <td> {range2} </td>
    </tr>
    <tr class="underline">
      <td> {target3} </td>
      <td> <b><span>{res3}</span> <span class="red">{ret3}</span> </b> </td>
      <td> {unit3} </td>
      <td> <span class="red"> {flag3} </span> </td>
      <td> {range3} </td>
    </tr>
    <tr class="underline">
      <td> {target4} </td>
      <td> <b><span>{res4}</span> <span class="red">{ret4}</span> </b> </td>
      <td> {unit4} </td>
      <td> <span class="red"> {flag4} </span> </td>
      <td> {range4} </td>
    </tr>
    <tr class="underline">
      <td> {target5} </td>
      <td> <b><span>{res5}</span> <span class="red">{ret5}</span> </b> </td>
      <td> {unit5} </td>
      <td> <span class="red"> {flag5} </span> </td>
      <td> {range5} </td>
    </tr>
    <tr class="underline">
      <td> {target6} </td>
      <td> <b><span>{res6}</span> <span class="red">{ret6}</span> </b> </td>
      <td> {unit6} </td>
      <td> <span class="red"> {flag6} </span> </td>
      <td> {range6} </td>
    </tr>
    <tr class="underline">
      <td> {target7} </td>
      <td> <b><span>{res7}</span> <span class="red">{ret7}</span> </b> </td>
      <td> {unit7} </td>
      <td> <span class="red"> {flag7} </span> </td>
      <td> {range7} </td>
    </tr>
    <tr class="underline">
      <td> {target8} </td>
      <td> <b><span>{res8}</span> <span class="red">{ret8}</span> </b> </td>
      <td> {unit8} </td>
      <td> <span class="red"> {flag8} </span> </td>
      <td> {range8} </td>
    </tr>
    <tr class="underline">
      <td> {target9} </td>
      <td> <b><span>{res9}</span> <span class="red">{ret9}</span> </b> </td>
      <td> {unit9} </td>
      <td> <span class="red"> {flag9} </span> </td>
      <td> {range9} </td>
    </tr>
    <tr class="underline">
      <td> {target10} </td>
      <td> <b><span>{res10}</span> <span class="red">{ret10}</span> </b> </td>
      <td> {unit10} </td>
      <td> <span class="red"> {flag10} </span> </td>
      <td> {range10} </td>
    </tr>
    <tr class="underline">
      <td> {target11} </td>
      <td> <b><span>{res11}</span> <span class="red">{ret11}</span> </b> </td>
      <td> {unit11} </td>
      <td> <span class="red"> {flag11} </span> </td>
      <td> {range11} </td>
    </tr>
    <tr class="underline">
      <td> {target12} </td>
      <td> <b><span>{res12}</span> <span class="red">{ret12}</span> </b> </td>
      <td> {unit12} </td>
      <td> <span class="red"> {flag12} </span> </td>
      <td> {range12} </td>
    </tr>
    <tr class="underline">
      <td> {target13} </td>
      <td> <b><span>{res13}</span> <span class="red">{ret13}</span> </b> </td>
      <td> {unit13} </td>
      <td> <span class="red"> {flag13} </span> </td>
      <td> {range13} </td>
    </tr>
    <tr class="underline">
      <td> {target14} </td>
      <td> <b><span>{res14}</span> <span class="red">{ret14}</span> </b> </td>
      <td> {unit14} </td>
      <td> <span class="red"> {flag14} </span> </td>
      <td> {range14} </td>
    </tr>
    <tr class="underline">
      <td> {target15} </td>
      <td> <b><span>{res15}</span> <span class="red">{ret15}</span> </b> </td>
      <td> {unit15} </td>
      <td> <span class="red"> {flag15} </span> </td>
      <td> {range15} </td>
    </tr>
    <tr class="underline">
      <td> {target16} </td>
      <td> <b><span>{res16}</span> <span class="red">{ret16}</span> </b> </td>
      <td> {unit16} </td>
      <td> <span class="red"> {flag16} </span> </td>
      <td> {range16} </td>
    </tr>
    <tr class="underline">
      <td> {target17} </td>
      <td> <b><span>{res17}</span> <span class="red">{ret17}</span> </b> </td>
      <td> {unit17} </td>
      <td> <span class="red"> {flag17} </span> </td>
      <td> {range17} </td>
    </tr>
    <tr class="underline">
      <td> {target18} </td>
      <td> <b><span>{res18}</span> <span class="red">{ret18}</span> </b> </td>
      <td> {unit18} </td>
      <td> <span class="red"> {flag18} </span> </td>
      <td> {range18} </td>
    </tr>
  </table>

  <div style="margin-top: 10px; float:right; width: 300pt; text-align: center;">
    <b>
      Đắk Lắk, {DD} tháng {MM} năm {YYYY} <br>
      BÁC SĨ XÉT NGHIỆM <br><br><br><br>
      {doctor}
    </b>
  </div>

  <div class="pagebreak"></div>

  <table border="1" style="border-collapse: collapse; width: 100%;">
    <tr style="height: 80px">
      <td style="width: 80px; padding: 1px;">
        <img style="width: 78px; height: 78px" src="data:image/jpeg;base64,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" alt="">
      </td>
      <td style="text-align: center;"> 
        <div> <b class="red big"> THANHXUANPET HOSPITAL </b> </div>  
        <div> <b class="green"> BỘ PHẬN XÉT NGHIỆM </b> </div>  
        <div> <b> Địa chỉ: </b> 12, 14 Lê Đại Hành, TP. Buôn Ma Thuột, Đắk Lắk </div>
        <div> <b> Điện thoại: </b> 02626 290609 – Website: thanhxuanpet.com </div>  
      </td>
      <td style="text-align: center; vertical-align:middle; width: 120px;">  
        <div> Biểu mẫu số 1 </div>
        <div> Số kiểm tra: 21 </div>
      </td>
    </tr>
  </table>
  <div style="margin: 15px 0px; text-align: center;">
    <div>
      <b> 
        PHIẾU TRẢ LỜI KẾT QUẢ XÉT NGHIỆM SINH HÓA 
        <div class="green">
          (Gợi ý kết quả)
        </div>
      </b>
    </div>
  </div>

  <p> {restar1} </p>
  <p> {restar2} </p>
  <p> {restar3} </p>
  <p> {restar4} </p>
  <p> {restar5} </p>
  <p> {restar6} </p>
  <p> {restar7} </p>
  <p> {restar8} </p>
  <p> {restar9} </p>
  <p> {restar10} </p>
  <p> {restar11} </p>
  <p> {restar12} </p>
  <p> {restar13} </p>
  <p> {restar14} </p>
  <p> {restar15} </p>
  <p> {restar16} </p>
</body>
</html>