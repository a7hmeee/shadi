# قاعدة بيانات نظام إدارة المساعدات الإنسانية

## الجداول الأساسية

### beneficiaries

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف الأساسي |
| user_id | foreign key | مرتبط بـ users (فريد) |
| national_id | string | الرقم الوطني (فريد) |
| phone | string | رقم الهاتف (مفهرس) |
| alternative_phone | string (nullable) | رقم هاتف بديل |
| date_of_birth | date (nullable) | تاريخ الميلاد |
| gender | string (nullable) | الجنس |
| social_status | string | الحالة الاجتماعية |
| family_members_count | unsigned smallint | عدد أفراد الأسرة |
| children_count | unsigned smallint | عدد الأطفال |
| dependents_count | unsigned smallint | عدد المعالين |
| monthly_income | decimal | الدخل الشهري |
| has_disability | boolean | هل لديه إعاقة |
| disability_details | text (nullable) | تفاصيل الإعاقة |
| has_chronic_disease | boolean | هل لديه مرض مزمن |
| chronic_disease_details | text (nullable) | تفاصيل المرض المزمن |
| housing_status | string (nullable) | حالة السكن |
| need_level | string | مستوى الاحتياج |
| governorate | string (nullable) | المحافظة |
| city | string (nullable) | المدينة |
| address | text (nullable) | العنوان |
| notes | text (nullable) | ملاحظات |
| is_active | boolean | نشط |

**العلاقات:**
- belongsTo User
- hasMany AidRequest

### aid_requests

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف الأساسي |
| beneficiary_id | foreign key | مرتبط بـ beneficiaries |
| request_number | string | رقم الطلب (فريد) |
| aid_type | string | نوع المساعدة |
| title | string (nullable) | عنوان الطلب |
| description | text | الوصف |
| status | string | حالة الطلب |
| priority_level | string | مستوى الأولوية |
| priority_score | unsigned tinyint (nullable) | درجة الأولوية (يُحسب لاحقًا) |
| requested_amount | decimal (nullable) | المبلغ المطلوب |
| approved_amount | decimal (nullable) | المبلغ المعتمد |
| submitted_at | timestamp (nullable) | تاريخ التقديم |
| reviewed_at | timestamp (nullable) | تاريخ المراجعة |
| approved_at | timestamp (nullable) | تاريخ الاعتماد |
| rejected_at | timestamp (nullable) | تاريخ الرفض |
| delivered_at | timestamp (nullable) | تاريخ التسليم |
| reviewed_by | foreign key (nullable) | المراجع (users) |
| decision_by | foreign key (nullable) | صاحب القرار (users) |
| rejection_reason | text (nullable) | سبب الرفض |
| reviewer_notes | text (nullable) | ملاحظات المراجع |
| beneficiary_notes | text (nullable) | ملاحظات المستفيد |
| possible_duplicate | boolean | هل هو طلب مكرر محتمل |
| duplicate_of_request_id | foreign key (nullable) | مرتبط بطلب آخر |

**العلاقات:**
- belongsTo Beneficiary
- belongsTo User (reviewer)
- belongsTo User (decisionMaker)
- hasMany AidRequestAttachment
- hasOne AidDistribution
- belongsTo AidRequest (duplicatedFrom)
- hasMany AidRequest (possibleDuplicates)

### aid_request_attachments

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف الأساسي |
| aid_request_id | foreign key | مرتبط بـ aid_requests |
| uploaded_by | foreign key (nullable) | الرافع (users) |
| original_name | string | اسم الملف الأصلي |
| file_name | string | اسم الملف المخزن |
| file_path | string | مسار الملف |
| disk | string | القرن (افتراضي: public) |
| mime_type | string (nullable) | نوع الملف |
| file_size | unsigned bigint (nullable) | حجم الملف |
| document_type | string (nullable) | نوع المستند |
| description | string (nullable) | وصف |
| is_verified | boolean | تم التحقق |
| verified_by | foreign key (nullable) | المدقق (users) |
| verified_at | timestamp (nullable) | تاريخ التحقق |

**العلاقات:**
- belongsTo AidRequest
- belongsTo User (uploader)
- belongsTo User (verifier)

### aid_distributions

| العمود | النوع | الوصف |
|--------|-------|-------|
| id | bigint | المعرف الأساسي |
| aid_request_id | foreign key (unique) | مرتبط بـ aid_requests (طلب واحد = توزيع واحد) |
| distributed_by | foreign key (nullable) | الموزع (users) |
| delivered_to_name | string | اسم المستلم |
| delivered_to_national_id | string (nullable) | الرقم الوطني للمستلم |
| delivery_method | string (nullable) | طريقة التسليم |
| amount | decimal (nullable) | المبلغ الموزع |
| delivered_at | timestamp | تاريخ التسليم |
| receipt_number | string (nullable, unique) | رقم الإيصال |
| notes | text (nullable) | ملاحظات |

**العلاقات:**
- belongsTo AidRequest
- belongsTo User (distributor)

## الـEnums المستخدمة

| الـEnum | القيم | النطاق |
|---------|-------|--------|
| SocialStatus | single, married, divorced, widowed, separated | Beneficiary |
| NeedLevel | low, medium, high, critical | Beneficiary |
| HousingStatus | owned, rented, hosted, temporary, unsafe, homeless | Beneficiary |
| AidRequestStatus | draft, submitted, under_review, needs_information, approved, rejected, delivered, cancelled | AidRequest |
| AidType | food, cash, medical, housing, education, clothing, emergency, other | AidRequest |
| PriorityLevel | low, medium, high, critical | AidRequest |

## حقول مؤقتة (جاهزة للمرحلة القادمة)

- `priority_score` في aid_requests: موجود كحقل nullable لكن لا يوجد منطق احتساب بعد. سيتم احتسابه في المرحلة القادمة بناءً على عوامل الاستحقاق.
- `possible_duplicate` و `duplicate_of_request_id`: موجودان في aid_requests لكن لا يوجد كشف تكرار بعد. سيتم تنفيذ كشف التكرار في المرحلة القادمة.

## ما سيتم في المرحلة القادمة

- حساب درجة الأولوية (priority_score) بناءً على عوامل الاستحقاق.
- كشف التكرار والازدواجية بين الطلبات.
- واجهة Filament Resources لإدارة المستفيدين وطلبات المساعدة.
- نظام احتساب الأولوية وترتيب الطلبات.
- بوابة المستفيد العامة.