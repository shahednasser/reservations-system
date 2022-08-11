<?php
return [
  'exists'               => ':attribute غير موجود',
  'required'             => ':attribute مطلوب',
  'alpha_dash'           => ':attribute يجب أن يكون مؤلف من أحرف، أرقام، _ أو - فقط',
  'min'                  => [
    'string'  => 'طول :attribute يجب أن يكون على الأقل :min حروف'
  ],
  'confirmed'            => 'تأكيد :attribute غير صحيح',
  'unique'               => ':attribute موجود.',
    "accepted" => 'يجب قبول ال:attribute',

  'attributes' => [
    'username' => "المستخدم",
    "password" => "كلمة المرور",
    "date" => "التاريخ",
      "pledge" => "تعهد",
      "event_name" => "عنوان النشاط",
      "committee" => "اللجنة",
      "from_date" => "تاريخ البداية",
      "to_date" => "تاريخ النهاية"
  ]
];
