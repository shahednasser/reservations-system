<p>
    يمكنك الدخول الى صفحة حجز مقبول من خلال الرزنامة.
    @if($user->isAdmin())
        كما يمكن للمشرف أن يدخل الى صفحة أي حجز أو طلب حجز من خلال صفحة "جميع الحجوزات".
    @else
        كما يمكنك الدخول الى صفحات حجوزاتك وطلبات الحجوزات من خلال صفحة "حجوزاتي"
    @endif
</p>
<p>
    في جميع صفحات الحجوزات يمكنك طبع الحجز من خلال النقر على زر "طبع". سوف تحصل على إستمارة فيها كل معلومات الحجز.
</p>