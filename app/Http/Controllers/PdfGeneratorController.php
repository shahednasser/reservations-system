<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Reservation;
use App\TemporaryReservationPlace;
use App\ManualReservation;
use App\HospitalityRequirment;
use App\PlaceRequirment;
use App\ReligiousRequirment;
use App\Equipment;

require_once base_path(). '/vendor/autoload.php';

class PdfGeneratorController extends Controller
{

    public function __construct(){
      $this->middleware('auth');
    }

    public function generateLongReservation($id = null){
      $reservation = null;
      if($id){
        $reservation = Reservation::find($id);
        if(!$reservation){
          return request()->json(["error" => __("الحجز غير موجود")]);
        }
      }
      $mpdf = $this->getMPDF();
      $days = ["الإثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت", "الأحد"];
      $dates = $reservation ? $reservation->longReservation->longReservationDates()->get() : collect([]);
      $mpdf->DefHTMLHeaderByName('header', '<table style="width: 100%;">
        <tr>
          <td style="text-align: right;">
            <img src="'.public_path().'\assets\logo.png'.'" style="width: 100px; height: 100px;">
          </td>
          <td>
            <h5 style="text-align: center;"><u>'.__('طلب إقامة نشاط دوري مستمرّ').'</u></h5>
          </td>
        </tr>
      </table>');
      $mpdf->SetHTMLHeaderByName('header');
      $str = '<div class="reservation-details">
        <table style="width: 100%; text-align: right; font-size: 1.2rem;">
          <tr>
            <td style="width: 50%;">
              <span style="font-weight: bold;">'.__("الى: مركز ومسجد الحسن").'</span>
            </td>
            <td style="width: 50%;">
            <span style="font-weight: bold;">'.__("تاريخ الطلب").':</span> '.($reservation ? format_date($reservation->date_created) : '').'
            </td>
          </tr>
          <tr>
            <td style="width: 33%;"><span style="font-weight: bold;">'.__("من (مقدم الطلب)").':</span> '.($reservation ? $reservation->user()->withTrashed()->first()->name : '').'</td>
            <td style="width: 33%;"><span style="font-weight: bold;">'.__("صفته").':</span> '.($reservation ? $reservation->user()->withTrashed()->first()->position : '').'</td>
            <td style="width: 33%;"><span style="font-weight: bold;">'.__("لجنة").':</span> '.($reservation ? $reservation->committee : '').'</td>
          </tr>'.(
            !$reservation ?
            '<tr>
              <td colspan="3">
                '.__('نرجو الموافقة على إقامة نشاط دوري مستمر ضمن حرم مركز ومسجد الحسن وفق المعلومات التالية').':
              </td>
            </tr>' : ''
            ).'
          <tr>
            <td>
              <span style="font-weight: bold;">'.__('نوع النشاط').':</span> '.($reservation ? $reservation->event_name : '').'
            </td>
          </tr>
          <tr>
          <td style="width:50%;">
            <span style="font-weight: bold;">'.__("تاريخ البداية").':</span> '.($reservation ? format_date($reservation->longReservation->from_date) : '').'
          </td>
          <td style="width:50%;">
            <span style="font-weight: bold;">'.__("تاريخ الانتهاء").':</span> '.($reservation ? format_date($reservation->longReservation->to_date) : '').'
          </td>
          </tr>'.(
            !$reservation ?
            '<tr>
              <td colspan="3">
                <small>
                  *'.__('ضع علامة عند اليوم المطلوب فقط * إستخدم بصيغة (24/24) * حدّد المكان بدقة واذكر رقم الغرفة * إستعمل الأرقام باللغة الأجنبية').'
                </small>
              </td>
            </tr>' : ''
            ).'
        </table>
        <table class="table table-bordered" style="margin-top: 1em;">
          <thead>
            <tr>
              <th scope="col"></th>
              <th scope="col">'.__('اليوم').'</th>
              <th scope="col">'.__("من الساعة (24\\24)").'</th>
              <th scope="col">'.__("الى الساعة (24\\24)").'</th>
              <th scope="col">'.__("النشاط").'</th>
              <th scope="col">'.__("المكان").'</th>
            </tr>
          </thead>
          <tbody>';
          for($day = 0; $day < 7; $day++){
            $date = $dates->where("day_of_week", $day + 1 === 7 ? 0 : $day + 1)->first();
            $str .= '<tr>
              <th scope="row" style="font-family: sans-serif; font-size: 1.5rem;">'.($date ? html_entity_decode('&#x2713;') : '').'</th>
              <td>'.$days[$day].'</td>
              <td>'.($date ? format_time_without_seconds($date->from_time) : '').'</td>
              <td>'.($date ? format_time_without_seconds($date->to_time) : '').'</td>
              <td>'.($date && $date->event ? $date->event : '').'</td>
              <td>';
            if($date){
              $places = $date->longReservationPlaces()->get();
              $first = true;
              foreach($places as $place){
                if(!$first){
                  $str .= "<br>";
                } else {
                  $first = false;
                }
                if($place->room){
                  $str .=( $place->room->name ? $place->room->name : "الغرفة ".$place->room->room_number)." -";
                }
                $str .= $place->floor->name;
              }
            }
            $str .= "</td>
            </tr>";
          }
          $str .= '</tbody>
                </table>
                  <table style="width: 100%; text-align: right;">
                  <tr>
                    <td style="width:100%">
                      <span style="font-weight: bold;">'.__("ملاحظات").':</span> '.($reservation ? $reservation->notes : '').'
                    </td>
                  </tr>
                  <tr>
                    <td style="width:100%">
                      <span style="font-weight: bold;">'.__("الأساتذة المشرفون").':</span> '.($reservation ? $reservation->supervisors : '').'
                    </td>
                  </tr>
                </table>'.(
                  !$reservation ? '
                  <div style="text-align: right; border: 1px solid; padding-right: .5rem; padding-bottom: .5em;" class="new-font">
                  '.__("تعهد").':<br>
                  '.__("أنا مقدم الطلب أتعهد بما يلي").':<br>
                  '.__("التقيد بالمكان والزمان الزّمان المصرّح إقامة النّشاط فيه وفق هذا الطّلب، والمحافظة على نظافته وسلامة كافّة تجهيزاته.").'<br>
                  '.__("إبلاغ موظفي أمانة المبنى في حال حدوث أي ضرر في التجهيزات والموجودات خلال النشاط.").'<br>
                  '.__("إبلاغ الإدارة <u>فوراً</u> إذا <u>أُلغِي أو تأجّل</u> النّشاط").'
                  </div>
                  <div style="text-align: right; margin-top: .5em;" class="new-font">
                    '.__('لا يعتبر الحجز نافذاً إلا بتوقيع الإدارة').'
                  </div>
                  <table style="margin-top: 1em; width: 100%;">
                    <tr>
                      <td style="width: 50%; text-align: right;">
                        '.__('<u>إسم</u> وتوقيع مقدم الطلب').'
                      </td>
                      <td style="width: 50%; text-align: left;">
                        '.__('توقيع الإدارة').'
                      </td>
                    </tr>
                  </table>
                  ': ''
                  ).'
              </div>';
      $mpdf->WriteHTML($str, 2);
      return $mpdf->Output($reservation ? $reservation->event_name : __('طلب إقامة نشاط دوري مستمرّ'), \Mpdf\Output\Destination::INLINE);
    }

    public function generateTempReservation($id = null){
      $reservation = null;
      if($id){
        $reservation = Reservation::find($id);
        if(!$reservation){
          return request()->json(["error" => __("الحجز غير موجود")]);
        }
      }
      $mpdf = $this->getMPDF();
      $mpdf->DefHTMLHeaderByName('header', '<table style="width: 100%;">
        <tr>
          <td style="text-align: right;">
            <img src="'.public_path().'\assets\logo.png'.'" style="width: 100px; height: 100px;">
          </td>
          <td>
            <h5 style="text-align: center;"><u>'.__('طلب إقامة نشاط').'</u></h5>
          </td>
        </tr>
      </table>');
      $mpdf->SetHTMLHeaderByName('header');
      $str = '<table class="reservation-table">
        <tr>
          <td colspan="4" style="text-align: center;">
            <span style="font-weight: bold;">'.__("تاريخ تقديم الطلب").': </span>'.($reservation ? format_date($reservation->date_created) : '').'
          </td>
        </tr>'.(
          !$reservation ?
          '<tr style="text-align: right;">
            <td colspan="2">
              '.__("*يقدم هذا الطلب حصراً خلال أوقات الدوام الرسمي").'
            </td>
            <td colspan="2">
              '.__("* يُرجى كتابة الوقت بصياغة 24/24 وإستعمال الأرقام باللغة الأجنبية").'
            </td>
          </tr>' : ''
          ).'
        <tr>
          <td colspan="4" style="text-align: right;">
            <span style="font-weight: bold;">'.__("من").':</span> '.($reservation ? $reservation->committee : '' ).'
          </td>
        </tr>
        '.(
          !$reservation ?
            '<tr>
              <td colspan="4" style="text-align: right;">
                '.__("نرجو الموافقة على إقامة النّشاط التّالي").':
              </td>
            </tr>'
          : ''
          ).'
          <tr style="text-align: right;">
            <td>
              <span style="font-weight: bold;">1) '.__('عنوان النّشاط').':</span>
            </td>
            <td colspan="3">
              '.($reservation ?
              $reservation->event_name
              : '').'
            </td>
          </tr>
          <tr style="text-align: right;">
            <td>
            <span style="font-weight: bold;">  2) '.__('تاريخ إقامته').':</span>
            </td>';
          $dates = $reservation ? $reservation->temporaryReservation->temporaryReservationDates()->get()->toArray() : [];
          for($i = 0; $i < 3; $i++){
            $date = null;
            if(isset($dates[$i])){
              $date = $dates[$i];
            }
            if($i != 0){
              $str .= "<tr><td></td>";
            }
            $str .= '<td> - '.($date ? format_date($date['date']) : '').'
            </td>
            <td>
            <span style="font-weight: bold;">'.__("من الساعة").':</span> '.
            ($date ? format_time_without_seconds($date['from_time']) : '').'
            </td>
            <td>
            <span style="font-weight: bold;">'.__("حتى الساعة").': </span>'.
            ($date ? format_time_without_seconds($date['to_time']) : '').'
            </td></tr>';
          }
          $str .= '<tr style="text-align: right;">
            <td colspan="4">
              <span style="font-weight: bold;">'.__("مكان النشاط").':</span>
            </td>
          </tr>';
          $places = $reservation ? $reservation->temporaryReservation->temporaryReservationPlaces()->get() : collect([]);
          foreach($places as $place){
              $str .= "<tr style='text-align: right;'>
                <td colspan='4'>
                  - ".($place ? ($place->floor ? ($place->floor->name." ".($place->room ? (
                    $place->room->name ? ' - '.$place->room->name :  __(" الغرفة ").$place->room->room_number
                    ) : '')) : '') : '')."
                </td>
              </tr>";
          }
          $str .= "<tr style='text-align: right;'>
            <td>
              <span style=\"font-weight: bold;\">4) ".__("المشرفون أثناء النشاط").":</span>
            </td>
            <td colspan='3'>
            ".($reservation ? ($reservation->supervisors ? $reservation->supervisors : '') : '')."
            </td>
          </tr>
          <tr style='text-align: right;'>
            <td colspan='4'>
              <span style=\"font-weight: bold;\">5) ".__('المستلزمات المطلوبة').":</span>
            </td>
          </tr>
          <tr>
            <td colspan='4'>
              - ".($reservation ? $reservation->temporaryReservation->equipment_needed_1 : '')."
            </td>
          </tr>
          <tr>
            <td colspan='4'>
              - ".($reservation ? $reservation->temporaryReservation->equipment_needed_2 : '')."
            </td>
          </tr>
          <tr>
            <td colspan='4'>
              - ".($reservation ? $reservation->temporaryReservation->equipment_needed_3 : '')."
            </td>
          </tr>
          <tr>
            <td colspan='4'>
              <span style=\"font-weight: bold;\">".__("ملاحظات إضافية").":</span>
            </td>
          </tr>
          <tr>
            <td colspan='4'>
              ".($reservation ? $reservation->notes : '')."
            </td>
          </tr>
          </table>
          ".(
            !$reservation ? '
            <div style="text-align: right; border: 1px solid; padding-right: .5rem; margin-top: 1em; padding-bottom: .5em;" class="new-font">
            '.__("تعهد").':<br>
            '.__("أنا مقدم الطلب أتعهد بما يلي").':<br>
            '.__("التقيد بالمكان والزمان الزّمان المصرّح إقامة النّشاط فيه وفق هذا الطّلب، والمحافظة على نظافته وسلامة كافّة تجهيزاته.").'<br>
            '.__("إبلاغ موظفي أمانة المبنى في حال حدوث أي ضرر في التجهيزات والموجودات خلال النشاط.").'<br>
            '.__("إبلاغ الإدارة <u>فوراً</u> إذا <u>أُلغِي أو تأجّل</u> النّشاط").'
            </div>
            <table style="margin-top: 1em; width: 100%;">
              <tr>
                <td style="width: 50%; text-align: right;">
                  '.__('<u>إسم</u> وتوقيع صاحب الطلب').'
                </td>
                <td style="width: 50%; text-align: left;">
                  '.__('توقيع الإدارة').'
                </td>
              </tr>
            </table>
            ': ''
            );
        $mpdf->WriteHTML($str, 2);
        $mpdf->Output($reservation ? $reservation->event_name : __('طلب إقامة نشاط'), \Mpdf\Output\Destination::INLINE);
    }

    public function generateManualReservation($id = null){
      $reservation = null;
      if($id){
        $reservation = ManualReservation::find($id);
        if(!$reservation){
          return request()->json(["error" => __("الحجز غير موجود")]);
        }
      }
      $mpdf = $this->getMPDF();
      $mpdf->DefHTMLHeaderByName('header', '<table style="width: 100%;">
        <tr>
          <td style="text-align: right; width: 33%;">
            <img src="'.public_path().'\assets\logo.png'.'" style="width: 100px; height: 100px;">
          </td>
          <td style="border: 2px solid #000; width: 36%;
                      text-align: center;">
            <h5 style="text-align: center;">'.__('طلب حجز قاعة مركز الحسن').'</h5>
          </td>
          <td style="width: 31%;">

          </td>
        </tr>
      </table>');
      $mpdf->SetHTMLHeaderByName('header');

      $str = '<table class="reservation-table align-bottom">
        <tr>
          <td style="width: 17%;">
            '.__('الإسم الثلاثي لطالب الحجز').':
          </td>
          <td class="bottom-border" style="width: 17%;">
            '.($reservation ? $reservation->full_name : '').'
          </td>
          <td style="width: 11%;">
            '.__('جمعية').':
          </td>
          <td class="bottom-border" style="width: 11%;">
            '.($reservation ? $reservation->organization : '').'
          </td>
          <td style="width: 11%;">
            '.__('خليوي').':
          </td>
          <td class="bottom-border" style="width: 11%;">
            '.($reservation ? $reservation->mobile_phone : '').'
          </td>
          <td style="width: 11%;">
            '.__('أرضي').':
          </td>
          <td class="bottom-border" style="width: 11%;">
            '.($reservation ? $reservation->home_phone : '').'
          </td>
        </tr>
        <tr>
          <td>
            '.__('العنوان').':
          </td>
          <td class="bottom-border" colspan="2">
            '.($reservation ? $reservation->event_name : '').'
          </td>
          <td>
            '.__('نوع المناسبة').':
          </td>
          <td class="bottom-border">
            '.($reservation ? $reservation->event_type : '').'
          </td>
          <td colspan="2">
            '.__('تاريخ تقديم الطلب').':
          </td>
          <td class="bottom-border">
            '.($reservation ? format_date($reservation->date_created) : '').'
          </td>
        </tr></table>';
      $str .= '<table class="reservation-table" style="font-size: .8rem; margin-top: 2em; margin-bottom: 2em;">';
      $mrds = $reservation ? $reservation->ManualReservationsDates()->get() : collect([]);
      $manualReservationsDates = $mrds->toArray();
      for($i = 0; $i < 3; $i++){
        $mrd = null;
        if(isset($manualReservationsDates[$i])){
          $mrd = $manualReservationsDates[$i];
        }

        $str .= '<tr>
          <td style="width: 10%;">
            '.($i + 1).'- '.__('تاريخ الحجز').'
          </td>
          <td class="bottom-border" style="width: 10%;">
            '.($mrd ? format_date($mrd["date"]) : '').'
          </td>
          <td style="width: 10%;">
            '.__('من السّاعة').':
          </td>
          <td class="bottom-border" style="width: 10%;">
            '.($mrd ? format_time_without_seconds($mrd["from_time"]) : '').'
          </td>
          <td style="width: 10%;">
            '.__('الى السّاعة').':
          </td>
          <td class="bottom-border" style="width: 10%;">
            '.($mrd ? format_time_without_seconds($mrd["to_time"]) : '').'
          </td>
          <td style="width: 10%;">
            '.__('رجال').'
          </td>
          <td class="bottom-border" style="width: 10%; font-family: sans-serif; font-size: 1.5rem;">
            '.($mrd ? ($mrd["for_men"] ? html_entity_decode('&#x2713;') : '') : '').'
          </td>
          <td style="width: 10%;">
            '.__('نساء').'
          </td>
          <td class="bottom-border" style="width: 10%; font-family: sans-serif; font-size: 1.5rem;">
            '.($mrd ? ($mrd["for_women"] ? html_entity_decode('&#x2713;') : '') : '').'
          </td>
        </tr>';
      }

      $str .= '</table>
              <div class="text-center full-border new-font">
              '.__('شروط الحجز').'
              </div>
                <div class="text-right new-font" style="margin-top: 1em;">
                  '.__(' قاعة مركز الحسن لها خصوصية معينة تتمثل في كونها تقع في مبنى يضم مسجداً ومركزاً إسلامياً، بناءً عليه يجب على طالب الحجز أن يتعهّد مسبقاً الإلتزام بالشّروط التّالية').':
                </div>
                <ol class="text-right new-font">
                  <li>
                  '.__(' التّدخين ممنوع في القاعة وفي أرجاء المبنى بتاتاً.').'
                  </li>
                  <li>
                  '.__('التّقيّد التّام بمواعيد هذا الطّلب وكافة بنوده.').'
                  </li>
                  <li>
                  '.__('توقيف الأجهزة الصّوتيّة عند موعد الآذان وحتّى الإنتهاء من صلاة الجّماعة.').'
                  </li>
                  <li>
                    '.__('فصل مقاعد الرّجال عن النّساء, علماً أن القاعة لاتستقبل أعراس النساء ولا الأعراس المختلطة.').'
                  </li>
                  <li>
                    '.__(' إلتزام الحشمة في اللّباس والتّقيّد بالأناشيد الدّينيّة فقط.').'
                  </li>
                  <li>
                    '.__('في حال إلغاء المناسبة , يتوجب على مقدّم هذا الطلب إبلاغ موظف الحجوزات ضمن أوقات الدوام الرسمي قبل 24 ساعة من موعد الحجز كحد أقصى وإلا يخسر 50% من القيمة المالية الواردة في المجموع العام لهذا الطلب كعطل وضرر .').'
                  </li>
                </ol>
                <table class="reservation-table table-bordered">
                  <tr>
                    <td colspan="4" style="border: none;">
                    </td>
                    <td>
                      '.__('السّعر').'
                    </td>
                    <td>
                      '.__('عدد الأيام').'
                    </td>
                    <td colspan="2">
                      '.__('التاريخ').'
                    </td>
                    <td>
                      '.__('المجموع').'
                    </td>
                  </tr>';
          $placeRequirments = PlaceRequirment::all();
          $mprs = $reservation ? $reservation->manualPlaceRequirments()->get() : collect([]);
          $prTotal = 0;
          $grandTotal = 0;
          foreach($placeRequirments as $placeRequirment){
            $mpr = $mprs->where('place_requirment_id', $placeRequirment->id)->first();
            $mrd = [];
            $mrds->each(function($item, $key) use(&$mrd, $placeRequirment){
              $mprds = $item->manualPlaceRequirmentsDates()->get();
              foreach($mprds as $mprd){
                $mpr = $mprd->manualPlaceRequirment;
                if($mpr && $mpr->placeRequirment->id == $placeRequirment->id){
                  $mrd[] = $item;
                }
              }
            });
            $total = $mpr ? $mpr->nb_days * $placeRequirment->price : 0;
            $prTotal += $total;
            $str .= '<tr>
              <td colspan="4">
                '.__($placeRequirment->name).'
              </td>
              <td>
                $'.$placeRequirment->price.'
              </td>
              <td>
                '.($mpr ? $mpr->nb_days : '').'
              </td>
              <td colspan="2">';
              if(count($mrd) > 0){
                foreach($mrd as $key => $m){
                  if($key != 0){
                    $str .= "<br />";
                  }
                  $str .= format_date($m->date);
                }
              }
              $str .= '</td>
              <td class="text-left">
                $'.($reservation ? $prTotal : '').'
              </td>
            </tr>';
          }
          $str .= '<tr>
            <td colspan="7" style="border: none;">
            </td>
            <td style="border-left: none; text-align: right;">
              '.__('المجموع').'
            </td>
            <td colspan="2" style="border-right: none; text-align: left;">
              $'.($reservation ? $prTotal : '').'
            </td>
          </tr></table>
          <table class="table-bordered reservation-table">
            <tr>
              <td colspan="10" style="text-align: center;">
                '.__('مستلزمات الضيافة').'
              </td>
            </tr>
            <tr>
              <td colspan="4" rowspan="2">
                '.__('المستلزمات').'
              </td>
              <td rowspan="2">
                '.__('السعر الفردي').'
              </td>
              <td colspan="5">
                '.__('كلفة مستلزمات الضيافة').'
              </td>
            </tr>
            <tr>
              <td colspan="2">
                '.__('عدد الأيام').'
              </td>
              <td colspan="3">
                '.__('المجموع').'
              </td>
            </tr>';
          $grandTotal += $prTotal;
          $hospitalityRequirments = HospitalityRequirment::all();
          $mhrs = $reservation ? $reservation->manualHospitalityRequirments()->get() : collect([]);
          $hospitalityTotal = 0;
          foreach($hospitalityRequirments as $hospitalityRequirment){
            $mhr = $mhrs->where('hospitality_requirment_id', $hospitalityRequirment->id)->first();
            $total = $mhr ? ($mhr->additional_price ? $mhr->additional_price * $mhr->nb_days : $mhr->nb_days *  $hospitalityRequirment->price) :
                    0;
            $hospitalityTotal += $total;
            $str .= '<tr>
              <td colspan="4">
                '.($hospitalityRequirment->price ? $hospitalityRequirment->name : ($mhr ? $mhr->additional_name : $hospitalityRequirment->name)).'
              </td>
              <td>
                '.($hospitalityRequirment->price ? '$'.$hospitalityRequirment->price : ($mhr ? '$'.$mhr->additional_price : '')).'
              </td>
              <td colspan="2">
                '.($mhr ? $mhr->nb_days : ($reservation ? '0' : '')).'
              </td>
              <td colspan="3" class="text-left">
                $'.($reservation ? $total : '').'
              </td>
            </tr>';
          }
          $grandTotal += $hospitalityTotal;
          $str .= '<tr>
            <td colspan="5" style="border:none;">
            </td>
            <td colspan="3" style="text-align: right; border-left: none;">
              '.__('المجموع').'
            </td>
            <td colspan="2" style="text-align: left; border-right: none;">
              $'.($reservation ? $hospitalityTotal : '').'
            </td>
          </tr>
          <tr>
            <td colspan="10" style="text-align: center;">
              '.__('المستلزمات الدّينيّة').'
            </td>
          </tr>
          <tr>
            <td colspan="4" rowspan="2">
            '.__('المستلزمات').'
            </td>
            <td rowspan="2">
            '.__('السّعر اليومي').'
            </td>
            <td colspan="5">
              '.__('كلفة مستلزمات الدّينيّة').'
            </td>
          </tr>
          <tr>
            <td colspan="2">
              '.__('عدد الأيام').'
            </td>
            <td colspan="3">
              '.__('المجموع').'
            </td>
          </tr>';
          $religiousRequirments = ReligiousRequirment::all();
          $mrrs = $reservation ? $reservation->manualReligiousRequirments()->get() : collect([]);
          $total = 0;
          $rrtotal = 0;
          foreach($religiousRequirments as $religiousRequirment){
            $mrr = $mrrs->where('religious_requirment_id', $religiousRequirment->id)->first();
            $total = $mrr ? $mrr->nb_days * $religiousRequirment->price : 0;
            $rrtotal += $total;
            $str .= '<tr>
              <td colspan="4">
                '.__($religiousRequirment->name).'
              </td>
              <td>
                '.($religiousRequirment->price == 0 ? __('مجاناً') : '$'.$religiousRequirment->price).'
              </td>
              <td colspan="2">
                '.($mrr ? $mrr->nb_days : ($reservation ? '0' : '')).'
              </td>
              <td colspan="3" class="text-left">
                $'.($reservation ? $total : '').'
              </td>
            </tr>';
          }
          $grandTotal += $rrtotal;
          $str .= '<tr>
            <td colspan="5" style="border: none;">
            </td>
            <td colspan="3" style="text-align: right; border-left: none;">
              '.__('المجموع').'
            </td>
            <td colspan="2" style="text-align: left; border-right: none;">
              $'.($reservation ? $rrtotal : '').'
            </td>
          </tr>';
          $grandTotal = $reservation ? $grandTotal - $reservation->discount : $grandTotal;
          $str .= '<tr>
          <td colspan="10" style="border:none;">
          </td>
          </tr>
          <tr>
          <td colspan="5" style="border:none;">
          </td>
            <td colspan="3" style="text-align: right; border-left: none;">
              '.__('خصم').'
            </td>
            <td colspan="2" style="text-align: left; border-right: none;">
              $'.($reservation ? $reservation->discount : '').'
            </td>
          </tr>
          <tr>
            <td colspan="5" style="border:none;">
            </td>
            <td colspan="3" style="font-size: 2rem;">
              '.__('المجموع العام').'
            </td>
            <td colspan="2" style="font-size: 2rem; text-align: left;">
              $'.($reservation ? $grandTotal : '').'
            </td>
          </tr>
          </table>';
            $str .= '<div class="mt-4 text-right">
            '.__('أنا الموقّع أدناه مقدّم هذا الطلب , ألتزم بكافّة الشّروط الواردة فيه  مع ضمان تكلفة الأضرار في حال وقوعها. ').'
            </div>
            <table class="reservation-table">
              <tr>
                <td style="width: 20%;">
                  '.__('الإسم الكامل').'
                </td>
                <td style="width: 30%;" class="bottom-border">
                </td>
                <td style="width: 15%;">
                  '.__('التوقيع').'
                </td>
                <td style="width: 30%;" class="bottom-border">
                </td>
              </tr>
              <tr>
                <td>
                  '.__('دفعة أولى').'
                </td>
                <td class="bottom-border">
                </td>
                <td>
                  '.('تتمة الدفعة').'
                </td>
                <td class="bottom-border">
                </td>
              </tr>
              <tr>
                <td>
                  '.__('ملاحظات').'
                </td>
                <td colspan="3" class="bottom-border">
                </td>
              </tr>
              <tr>
                <td colspan="4" class="bottom-border" style="padding-top: 1.5em;">
                </td>
              </tr>
            </table>';
          $equipments = Equipment::all();
          $mres = $reservation ? $reservation->manualReservationEquipments()->get() : collect([]);
          $str .= '<pagebreak></pagebreak><div class="text-right">
            '.__('حقل خاص للموظفين').':
          </div>
          <table class="table-bordered">
            <tr>
              <td>
                '.__('مستلزمات اليوم الأول').'
              </td>
              <td>
                '.__('العدد').'
              </td>
              <td>
                '.__('مستلزمات اليوم الثاني').'
              </td>
              <td>
                '.__('العدد').'
              </td>
              <td>
                '.__('مستلزمات اليوم الثالث').'
              </td>
              <td>
                '.__('العدد').'
              </td>
            </tr>';
          foreach($equipments as $equipment){
            $mre = collect($mres->where('equipment_id', $equipment->id)->all());
            $mre_1 = $mre ? $mre->where("day_nb", 1)->first() : null;
            $mre_2 = $mre ? $mre->where("day_nb", 2)->first() : null;
            $mre_3 = $mre ? $mre->where("day_nb", 3)->first() : null;
            $str .= "<tr>
              <td>
                ".__($equipment->name)."
              </td>
              <td>
                ".($mre_1 ? $mre_1->number : ($mres->count() ? 0 : ""))."
              </td>
              <td>
                ".__($equipment->name)."
              </td>
              <td>
                ".($mre_2 ? $mre_2->number : ($mres->count() ? 0 : ""))."
              </td>
              <td>
                ".__($equipment->name)."
              </td>
              <td>
                ".($mre_3 ? $mre_3->number : ($mres->count() ? 0 : ""))."
              </td>
            </tr>";
          }
          $str .= "</table><div class='text-left new-font'>
            ".__('توقيع الإدارة')."
          </div>";
          $mpdf->WriteHTML($str, 2);
          $mpdf->Output($reservation ? $reservation->event_type : __('طلب حجز قاعة مركز الحسن'), \Mpdf\Output\Destination::INLINE);
    }

    public function getMPDF(){
      $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
      $fontDirs = $defaultConfig['fontDir'];

      $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
      $fontData = $defaultFontConfig['fontdata'];
      $mpdf = new \Mpdf\Mpdf([
        "margin_top" => 40,
        'fontDir' => array_merge($fontDirs, [
            public_path().'\fonts',
        ]),
        'fontdata' => $fontData + [
            'amiri' => [
                'R' => 'Amiri-Regular.ttf',
                'I' => 'Amiri-Italic.ttf',
                'B' => 'Amiri-Bold.ttf',
                'useOTL' => 0xFF,
                'useKashida' => 75,
            ]
        ],
        'default_font' => 'amiri'
      ]);
      $mpdf->SetDirectionality('rtl');
      $mpdf->autoScriptToLang = true;
      $stylesheet = file_get_contents(public_path().'/css/bootstrap-pdf.css');
      $mpdf->WriteHTML($stylesheet,1);
      return $mpdf;
    }
}
