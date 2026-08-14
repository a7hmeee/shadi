<?php

namespace App\Domains\Identity\Enums;

enum PermissionName: string
{
    case DASHBOARD_VIEW = 'dashboard.view';

    case DECISION_SUPPORT_VIEW = 'decision_support.view';

    case BENEFICIARIES_VIEW = 'beneficiaries.view';
    case BENEFICIARIES_CREATE = 'beneficiaries.create';
    case BENEFICIARIES_UPDATE = 'beneficiaries.update';
    case BENEFICIARIES_DELETE = 'beneficiaries.delete';

    case AID_REQUESTS_VIEW = 'aid_requests.view';
    case AID_REQUESTS_CREATE = 'aid_requests.create';
    case AID_REQUESTS_REVIEW = 'aid_requests.review';
    case AID_REQUESTS_APPROVE = 'aid_requests.approve';
    case AID_REQUESTS_REJECT = 'aid_requests.reject';
    case AID_REQUESTS_MARK_DELIVERED = 'aid_requests.mark_delivered';

    case AID_REQUEST_ATTACHMENTS_VIEW = 'aid_request_attachments.view';
    case AID_REQUEST_ATTACHMENTS_UPLOAD = 'aid_request_attachments.upload';
    case AID_REQUEST_ATTACHMENTS_DELETE = 'aid_request_attachments.delete';
    case AID_REQUEST_ATTACHMENTS_VERIFY = 'aid_request_attachments.verify';

    case REPORTS_VIEW = 'reports.view';
    case EMPLOYEES_MANAGE = 'employees.manage';
    case SETTINGS_MANAGE = 'settings.manage';
}
