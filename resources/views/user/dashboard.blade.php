@extends('layouts.app')
@section('title', 'Кабинет')
@section('content')

<div class="wrapper">
<header class="header">
<div class="container">
<div class="header__inner">
<a class="header__logo logo" href="#"><img alt="" src="{{ asset('personal-acc/img/logo.svg') }}"/></a>
<div class="header__actions">
<button class="btn btn--light btn-support" data-support-btn="" type="button">
							Support
							<span class="btn__icon">
<img alt="support" src="{{ asset('personal-acc/img/icons/support.svg') }}"/>
</span>
</button>
<div class="desktop">
<button class="btn" data-popup="#withdraw-modal" type="button"><span>Withdrawal of funds</span>
</button>
</div>
<div class="mobile">
<a class="btn" href="{{ route('user.withdraw') }}">Withdrawal <span class="btn__icon">
<img alt="withdraw" src="{{ asset('personal-acc/img/icons/withdraw.svg') }}"/>
</span></a>
</div>
</div>
</div>
</div>
</header>
<main class="page">
<div class="container">
<div class="grid">
<div class="mobile-nav-tabs">
<button class="active" data-tab="main" type="button">
<span>Brokers</span>
<span>
<svg fill="none" height="18" viewbox="0 0 18 18" width="18" xmlns="http://www.w3.org/2000/svg">
<path d="M11.8267 5.85585C11.4438 5.91737 9.27407 6.28103 9.13728 6.28103C8.99996 6.28103 8.99996 6.28103 8.99996 6.28103C8.99996 6.28103 8.99996 6.28103 8.86264 6.28103C8.72532 6.28103 6.55607 5.91737 6.17318 5.85585C5.51509 5.74929 4.96578 5.93551 4.77351 6.01514C3.96601 6.35022 4.23738 7.13357 4.41646 7.42361C4.66421 7.82242 5.60408 8.86941 6.53023 8.99189C7.73764 9.15066 8.78023 8.24756 8.99996 8.24756C9.21912 8.24756 10.2623 9.15066 11.4697 8.99189C12.3958 8.86941 13.3363 7.82238 13.5835 7.42361C13.7625 7.13357 14.0339 6.35025 13.2264 6.01514C13.0336 5.93548 12.4848 5.74926 11.8267 5.85585ZM6.49177 7.76909C6.10175 7.39172 6.16658 7.01267 6.62143 6.9819C7.07737 6.95005 7.84532 7.14011 7.85794 7.39168C7.89036 8.02123 6.88179 8.14758 6.49177 7.76909ZM11.5081 7.76909C11.1176 8.14758 10.109 8.02123 10.142 7.39172C10.1546 7.14014 10.9226 6.95005 11.3774 6.98194C11.8333 7.0127 11.8982 7.39172 11.5081 7.76909Z" fill="currentColor"></path>
<path d="M14.1592 4.07482C14.1592 3.89795 13.9757 3.54417 13.6467 3.47277C13.1715 0.673453 10.426 0 9 0C7.57396 0 4.8285 0.673453 4.3528 3.47277C4.02321 3.54417 3.84082 3.89795 3.84082 4.07482C3.84082 4.25225 3.84082 5.24377 3.84082 5.24377H14.1592C14.1592 5.24377 14.1592 4.25225 14.1592 4.07482Z" fill="currentColor"></path>
<path d="M12.5409 13.4377C12.5409 12.7274 12.3201 11.9507 11.6186 11.9507H6.38083C5.67989 11.9507 5.45854 12.7274 5.45854 13.4377C5.45854 13.6063 2.17639 14.4512 2.17639 16.3436C2.17639 16.7836 3.24592 17.9997 8.96264 17.9997H9.03678C14.7541 17.9997 15.8236 16.7835 15.8236 16.3436C15.8236 14.4512 12.5409 13.6063 12.5409 13.4377Z" fill="currentColor"></path>
</svg>
</span>
</button>
<button data-tab="aside" type="button">
<span>Transactions</span>
<span>
<svg fill="none" height="18" viewbox="0 0 19 18" width="19" xmlns="http://www.w3.org/2000/svg">
<path d="M4.4091 5.65924C4.4091 3.9926 5.7652 2.6365 7.43184 2.6365H13.2438C13.0344 1.90404 12.3669 1.36377 11.5682 1.36377H4.25001C3.28464 1.36377 2.5 2.14841 2.5 3.11378V12.9775C2.5 13.9428 3.28464 14.7275 4.25001 14.7275H4.4091V5.65924Z" fill="currentColor"></path>
<path d="M14.75 3.90918H7.43183C6.46646 3.90918 5.68182 4.69382 5.68182 5.65919V14.8865C5.68182 15.8519 6.46646 16.6365 7.43183 16.6365H14.75C15.7154 16.6365 16.5001 15.8519 16.5001 14.8865V5.65919C16.5001 4.69382 15.7154 3.90918 14.75 3.90918ZM13.4773 14.7274H8.70456C8.44111 14.7274 8.22729 14.5136 8.22729 14.2501C8.22729 13.9867 8.44111 13.7729 8.70456 13.7729H13.4773C13.7408 13.7729 13.9546 13.9867 13.9546 14.2501C13.9546 14.5136 13.7408 14.7274 13.4773 14.7274ZM13.4773 12.1819H8.70456C8.44111 12.1819 8.22729 11.9681 8.22729 11.7047C8.22729 11.4412 8.44111 11.2274 8.70456 11.2274H13.4773C13.7408 11.2274 13.9546 11.4412 13.9546 11.7047C13.9546 11.9681 13.7408 12.1819 13.4773 12.1819ZM13.4773 9.95466H8.70456C8.44111 9.95466 8.22729 9.74084 8.22729 9.47738C8.22729 9.21393 8.44111 9.00011 8.70456 9.00011H13.4773C13.7408 9.00011 13.9546 9.21393 13.9546 9.47738C13.9546 9.74084 13.7408 9.95466 13.4773 9.95466ZM13.4773 7.40919H8.70456C8.44111 7.40919 8.22729 7.19537 8.22729 6.93192C8.22729 6.66846 8.44111 6.45464 8.70456 6.45464H13.4773C13.7408 6.45464 13.9546 6.66846 13.9546 6.93192C13.9546 7.19537 13.7408 7.40919 13.4773 7.40919Z" fill="currentColor"></path>
</svg>
</span>
</button>
</div>
@include('client.dashboard.partials.overview')
<div class="chat" data-support-window="">
<div class="chat__head">
<div class="chat__item">
<img alt="person" src="{{ asset('personal-acc/img/icons/person-support.svg') }}"/>
<span>Support Service</span>
</div>
<button class="chat__close" type="button">
<svg fill="none" height="20" viewbox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
<path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
</svg>
</button>
</div>
<div class="chat__body">
<div class="chat__list">
<div class="chat__item">Hello)</div>
<div class="chat__item">Can i help you?</div>
<div class="chat__item chat__item--answer">Hi!</div>
</div>
</div>
<div class="chat__bottom">
<div class="field">
<input class="chat__input" placeholder="Write here..." type="text"/>
</div>
<button class="chat__submit" type="submit"><img alt="send" src="{{ asset('personal-acc/img/icons/send.svg') }}"/></button>
<label class="chat__file">
<input hidden="" type="file"/>
<span><img alt="attach" src="{{ asset('personal-acc/img/icons/attach.svg') }}"/></span>
</label>
</div>
</div>
</div>
</div>
</main>
</div>
<div aria-hidden="true" class="popup popup--md" id="violation">
<div class="popup__wrapper">
<div class="popup__content">
<button class="popup__close" data-close="" type="button">
<svg fill="none" height="20" viewbox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
<path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
</svg>
</button>
<div class="modal-content">
<div class="modal-content__top">
<div class="logo"><img alt="logo" src="{{ asset('personal-acc/img/logo.svg') }}"/></div>
<div class="modal-content__text">
<p>Describe your complaint</p>
</div>
</div>
<div class="modal-content__body">
<form action="#">
@csrf

<div class="field">
<textarea placeholder="Write here..."></textarea>
</div>
<button class="btn" type="submit">Send</button>
<label class="modal-content__file">
<input hidden="" type="file"/>
<span>Attach the file</span>
</label>
</form>
</div>
</div>
</div>
</div>
</div>
<div aria-hidden="true" class="popup popup--sm" id="create-modal">
<div class="popup__wrapper">
<div class="popup__content">
<div class="create-account">
<div class="create-account__title">Создание нового счёта</div>
<form action="#" class="create-account__form">
@csrf

<div class="field"><input placeholder="Название счёта (Account name)" type="text"/></div>
<div class="field"><input placeholder="Номер счёта (Account number)" type="text"/></div>
<div class="field"><input placeholder="Тип счёта (Account type)" type="text"/></div>
<div class="field"><input placeholder="Банк (Bank)" type="text"/></div>
<div class="field"><input placeholder="Инициалы клиента (Client's fullname)" type="text"/></div>
<div class="field"><input placeholder="Срок действия (Expiration date)" type="text"/></div>
<select>
<option disabled="" selected="" value="">Статус</option>
<option value="2">Active</option>
<option value="3">Hold</option>
<option value="4">Blocked</option>
</select>
<button class="btn btn--md" type="submit">Добавить новый счёт</button>
</form>
</div>
</div>
</div>
</div>
<div aria-hidden="true" class="popup" id="withdraw-modal">
<div class="popup__wrapper">
<div class="popup__content">
<button class="popup__close" data-close="" type="button">
<svg fill="none" height="20" viewbox="0 0 20 20" width="20" xmlns="http://www.w3.org/2000/svg">
<path d="M1 1L19 19M19 1L1 19" stroke="black" stroke-linecap="round" stroke-width="2"></path>
</svg>
</button>
<div class="modal-content">
<div class="modal-content__top">
<div class="logo"><img alt="logo" src="{{ asset('personal-acc/img/logo.svg') }}"/></div>
<div class="modal-content__text">
<p>Choose a withdrawal method</p>
</div>
</div>
<div class="modal-content__body">
<div class="tabs" data-tabs="">
<nav class="tabs__navigation" data-tabs-titles="">
<button class="tabs__title _tab-active" type="button">
<span>
										Withdrawal to the card
									</span>
<span>
<svg fill="none" height="40" viewbox="0 0 41 40" width="41" xmlns="http://www.w3.org/2000/svg">
<mask height="32" id="mask0_41_232" maskunits="userSpaceOnUse" style="mask-type:luminance" width="37" x="2" y="4">
<path d="M12.1666 10.8333V7.49998C12.1666 7.05795 12.3422 6.63403 12.6548 6.32147C12.9673 6.00891 13.3913 5.83331 13.8333 5.83331H35.5C35.942 5.83331 36.3659 6.00891 36.6785 6.32147C36.991 6.63403 37.1666 7.05795 37.1666 7.49998V22.5C37.1666 22.942 36.991 23.3659 36.6785 23.6785C36.3659 23.991 35.942 24.1666 35.5 24.1666H33.8333" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M27.1666 15.8333H5.49998C4.57951 15.8333 3.83331 16.5795 3.83331 17.5V32.5C3.83331 33.4205 4.57951 34.1666 5.49998 34.1666H27.1666C28.0871 34.1666 28.8333 33.4205 28.8333 32.5V17.5C28.8333 16.5795 28.0871 15.8333 27.1666 15.8333Z" fill="white" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M3.83331 23.3333H28.8333" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M28.8333 19.1666V29.1666M3.83331 19.1666V29.1666" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
<path d="M9.66663 28.3333H16.3333M21.3333 28.3333H23" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"></path>
</mask>
<g mask="url(#mask0_41_232)">
<path d="M0.5 0H40.5V40H0.5V0Z" fill="currentColor"></path>
</g>
</svg>
</span>
</button>
<button class="tabs__title" type="button">
<span>Withdrawal by IBAN</span>
<span><svg fill="none" height="40" viewbox="0 0 41 40" width="41" xmlns="http://www.w3.org/2000/svg">
<path d="M25.5 25V20H30.5V16.6666L37.1667 22.5L30.5 28.3333V25H25.5ZM23.8334 14.5V16.6666H3.83337V14.5L13.8334 8.33331L23.8334 14.5ZM3.83337 28.3333H23.8334V31.6666H3.83337V28.3333ZM12.1667 18.3333H15.5V26.6666H12.1667V18.3333ZM5.50004 18.3333H8.83337V26.6666H5.50004V18.3333ZM18.8334 18.3333H22.1667V26.6666H18.8334V18.3333Z" fill="currentColor"></path>
</svg>
</span>
</button>
<button class="tabs__title" type="button">
<span>Withdrawal to cryptocash</span>
<span><svg fill="none" height="40" viewbox="0 0 41 40" width="41" xmlns="http://www.w3.org/2000/svg">
<path d="M31.75 6.25H28.3617C27.8424 6.25036 27.3394 6.4314 26.939 6.76205C26.5386 7.09271 26.2658 7.55238 26.1672 8.06223C26.0687 8.57208 26.1506 9.10032 26.399 9.55637C26.6473 10.0124 27.0466 10.3678 27.5284 10.5617L30.9684 11.9383C31.4501 12.1322 31.8494 12.4876 32.0978 12.9436C32.3461 13.3997 32.428 13.9279 32.3295 14.4378C32.231 14.9476 31.9581 15.4073 31.5577 15.7379C31.1573 16.0686 30.6543 16.2496 30.135 16.25H29.25M29.25 6.25V5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
<path d="M30.55 21.1667C32.4003 20.9237 34.1458 20.1677 35.589 18.9844C37.0322 17.8011 38.1155 16.2376 38.7164 14.4708C39.3174 12.7039 39.412 10.8041 38.9895 8.9863C38.5671 7.16847 37.6445 5.50509 36.326 4.18426C35.0075 2.86343 33.3458 1.93785 31.5287 1.51217C29.7116 1.0865 27.8117 1.17772 26.0438 1.77552C24.2758 2.37331 22.7104 3.45384 21.5245 4.8949C20.3387 6.33597 19.5796 8.08009 19.3333 9.93003M26.75 26.25C26.75 22.9348 25.433 19.7554 23.0888 17.4112C20.7446 15.067 17.5652 13.75 14.25 13.75M4.24996 18.75C3.2367 20.1147 2.5124 21.6717 2.12134 23.3258C1.73029 24.9799 1.68068 26.6964 1.97557 28.3703C2.27045 30.0443 2.90362 31.6405 3.83638 33.0614C4.76914 34.4823 5.98189 35.6981 7.40048 36.6343C8.81908 37.5706 10.4137 38.2077 12.0869 38.5068C13.7601 38.8058 15.4767 38.7604 17.1318 38.3735C18.7869 37.9865 20.3457 37.2661 21.7128 36.2562C23.08 35.2463 24.2269 33.9682 25.0833 32.5" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
<path d="M15.5 26.25C16.163 26.25 16.7989 25.9866 17.2678 25.5178C17.7366 25.0489 18 24.413 18 23.75C18 23.087 17.7366 22.4511 17.2678 21.9822C16.7989 21.5134 16.163 21.25 15.5 21.25H11.75V31.25H15.5C16.163 31.25 16.7989 30.9866 17.2678 30.5178C17.7366 30.0489 18 29.413 18 28.75C18 28.087 17.7366 27.4511 17.2678 26.9822C16.7989 26.5134 16.163 26.25 15.5 26.25ZM15.5 26.25H11.75M14.25 21.25V18.75M14.25 31.25V33.75M1.75 7.5L5.5 13.75L11.75 10" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
<path d="M14.1484 1.5C11.8342 2.3433 9.86066 3.92279 8.5308 5.99601C7.20094 8.06923 6.58823 10.5216 6.78672 12.9767M39.2501 32.5L35.5001 26.25L29.2501 30" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
<path d="M26.8517 38.5C29.1653 37.6569 31.1384 36.0779 32.4682 34.0053C33.798 31.9328 34.4111 29.4812 34.2134 27.0267" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
</svg>
</span>
</button>
</nav>
<div class="tabs__content" data-tabs-body="">
<div class="tabs__body">
<form action="#">
@csrf

<div class="field has-error">
<input placeholder="1111 2222 3333 4444" type="number"/>
<span class="error-message">message error</span>
</div>
<div class="field">
<input placeholder="Fullname card holder" type="text"/>
<span class="error-message">message error</span>
</div>
<div class="field">
<input placeholder="Amount" type="number"/>
<span class="error-message">message error</span>
</div>
<button class="btn btn--md" disabled="" type="submit">Withdrawal</button>
</form>
</div>
<div class="tabs__body">
<form action="#">
@csrf

<div class="field">
<input placeholder="Enter IBAN" type="text"/>
<span class="error-message">message error</span>
</div>
<div class="field">
<input placeholder="BIC code" type="text"/>
<span class="error-message">message error</span>
</div>
<div class="field">
<input placeholder="Fullname bank account holder" type="text"/>
<span class="error-message">message error</span>
</div>
<div class="field">
<select>
<option selected="" value="">Country</option>
<option value="2">Пункт №2</option>
<option value="3">Пункт №3</option>
<option value="4">Пункт №4</option>
</select>
</div>
<div class="field">
<input placeholder="Name of the bank" type="text"/>
<span class="error-message">message error</span>
</div>
<div class="field">
<input placeholder="Amount" type="number"/>
<span class="error-message">message error</span>
</div>
<button class="btn btn--md" disabled="" type="submit">Withdrawal</button>
</form>
</div>
<div class="tabs__body">
<button class="btn-toggle-crypto-window" style="padding: 0.5rem; border-radius: 0.25rem; border: 1px solid #63616C; font-size: 0.625rem; margin-bottom: 0.8rem; margin-inline: auto;" type="button">click
										show crypto type window</button>
<div class="type-crypto-window">
<img alt="attention" src="{{ asset('personal-acc/img/icons/attention.svg') }}"/>
<p>At first you need to change the type of account to "Crypto" type</p>
</div>
<form action="#" class="form-crypto">
@csrf

<div class="field">
<input placeholder="Deposit address" type="text"/>
<span class="error-message">message error</span>
</div>
<div class="field">
<select>
<option selected="" value="">Network</option>
<option value="2">Пункт №2</option>
<option value="3">Пункт №3</option>
<option value="4">Пункт №4</option>
</select>
</div>
<div class="field">
<select>
<option selected="" value="">Coin</option>
<option value="2">Пункт №2</option>
<option value="3">Пункт №3</option>
<option value="4">Пункт №4</option>
</select>
</div>
<button class="btn btn--md" disabled="" type="submit">Withdrawal</button>
</form>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
@endsection
