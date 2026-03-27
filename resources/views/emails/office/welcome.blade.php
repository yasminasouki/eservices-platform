@component('mail::message')
# Hello {{ $name }}!

Your office staff account has been created on the **E-Services Platform**.

**Assigned Office:** {{ $officeName }}

---

**Your Login Credentials**

| | |
|---|---|
| **Email** | {{ $email }} |
| **Password** | {{ $password }} |

@component('mail::button', ['url' => route('login')])
Login Now
@endcomponent

> **Security Notice:** Please change your password after your first login.

Thanks,<br>
**E-Services Platform Team**
@endcomponent
