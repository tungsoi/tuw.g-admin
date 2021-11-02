<?php

namespace App\Console\Commands\System;

use App\Models\PaymentOrder\PaymentOrder;
use App\Models\System\ScheduleLog;
use Illuminate\Console\Command;

class DeleteNullItemPaymentOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:null-payment-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ids = PaymentOrder::select('id')
        ->where('status', 'payment_not_export')
        ->where('created_at', '>', "2021-09-01 00:00:01")
        ->doesntHave('transportCode')
        ->pluck('id');
        
        PaymentOrder::whereIn('id', $ids)->update([
            'status'    =>  'cancel'
        ]);

        ScheduleLog::create([
            'name'  =>  $this->signature . " - " . sizeof($ids)
        ]);
    }

    // 0 => 17382
    // 1 => 17424
    // 2 => 17530
    // 3 => 17531
    // 4 => 17532
    // 5 => 17533
    // 6 => 17534
    // 7 => 17536
    // 8 => 17537
    // 9 => 17542
    // 10 => 17543
    // 11 => 17549
    // 12 => 17553
    // 13 => 17555
    // 14 => 17558
    // 15 => 17563
    // 16 => 17564
    // 17 => 17566
    // 18 => 17569
    // 19 => 17572
    // 20 => 17575
    // 21 => 17577
    // 22 => 17580
    // 23 => 17581
    // 24 => 17583
    // 25 => 17585
    // 26 => 17587
    // 27 => 17591
    // 28 => 17593
    // 29 => 17594
    // 30 => 17600
    // 31 => 17603
    // 32 => 17607
    // 33 => 17609
    // 34 => 17620
    // 35 => 17621
    // 36 => 17624
    // 37 => 17632
    // 38 => 17634
    // 39 => 17637
    // 40 => 17638
    // 41 => 17651
    // 42 => 17654
    // 43 => 17688
    // 44 => 17755
    // 45 => 17776
    // 46 => 17783
    // 47 => 17790
    // 48 => 17802
    // 49 => 17808
    // 50 => 17819
    // 51 => 17823
    // 52 => 17824
    // 53 => 17870
    // 54 => 17872
    // 55 => 17877
    // 56 => 17878
    // 57 => 17880
    // 58 => 17885
    // 59 => 17888
    // 60 => 17899
    // 61 => 17944
    // 62 => 17946
    // 63 => 17947
    // 64 => 17948
    // 65 => 17949
    // 66 => 17950
    // 67 => 17951
    // 68 => 17953
    // 69 => 17954
    // 70 => 17955
    // 71 => 17956
    // 72 => 17957
    // 73 => 17958
    // 74 => 17959
    // 75 => 17960
    // 76 => 17964
    // 77 => 17965
    // 78 => 17967
    // 79 => 17968
    // 80 => 17969
    // 81 => 17970
    // 82 => 17971
    // 83 => 17972
    // 84 => 17973
    // 85 => 17976
    // 86 => 17977
    // 87 => 17978
    // 88 => 17979
    // 89 => 17980
    // 90 => 17981
    // 91 => 18019
    // 92 => 18020
    // 93 => 18024
    // 94 => 18026
    // 95 => 18039
    // 96 => 18040
    // 97 => 18045
    // 98 => 18053
    // 99 => 18067
    // 100 => 18075
    // 101 => 18076
    // 102 => 18080
    // 103 => 18081
    // 104 => 18082
    // 105 => 18083
    // 106 => 18084
    // 107 => 18085
    // 108 => 18090
    // 109 => 18092
    // 110 => 18093
    // 111 => 18094
    // 112 => 18096
    // 113 => 18098
    // 114 => 18100
    // 115 => 18101
    // 116 => 18102
    // 117 => 18105
    // 118 => 18106
    // 119 => 18107
    // 120 => 18108
    // 121 => 18109
    // 122 => 18110
    // 123 => 18111
    // 124 => 18114
    // 125 => 18115
    // 126 => 18116
    // 127 => 18118
    // 128 => 18120
    // 129 => 18121
    // 130 => 18124
    // 131 => 18125
    // 132 => 18126
    // 133 => 18127
    // 134 => 18128
    // 135 => 18131
    // 136 => 18133
    // 137 => 18134
    // 138 => 18135
    // 139 => 18141
    // 140 => 18191
    // 141 => 18192
    // 142 => 18281
    // 143 => 18282
    // 144 => 18283
    // 145 => 18285
    // 146 => 18286
    // 147 => 18288
    // 148 => 18293
    // 149 => 18294
    // 150 => 18295
    // 151 => 18296
    // 152 => 18297
    // 153 => 18298
    // 154 => 18300
    // 155 => 18301
    // 156 => 18303
    // 157 => 18308
    // 158 => 18309
    // 159 => 18313
    // 160 => 18318
    // 161 => 18321
    // 162 => 18327
    // 163 => 18329
    // 164 => 18331
    // 165 => 18332
    // 166 => 18333
    // 167 => 18334
    // 168 => 18337
    // 169 => 18343
    // 170 => 18344
    // 171 => 18345
    // 172 => 18347
    // 173 => 18348
    // 174 => 18350
    // 175 => 18351
    // 176 => 18352
    // 177 => 18353
    // 178 => 18354
    // 179 => 18356
    // 180 => 18357
    // 181 => 18358
    // 182 => 18359
    // 183 => 18360
    // 184 => 18361
    // 185 => 18362
    // 186 => 18363
    // 187 => 18364
    // 188 => 18365
    // 189 => 18367
    // 190 => 18368
    // 191 => 18369
    // 192 => 18370
    // 193 => 18371
    // 194 => 18373
    // 195 => 18374
    // 196 => 18375
    // 197 => 18377
    // 198 => 18379
    // 199 => 18383
    // 200 => 18384
    // 201 => 18385
    // 202 => 18386
    // 203 => 18387
    // 204 => 18388
    // 205 => 18389
    // 206 => 18390
    // 207 => 18391
    // 208 => 18392
    // 209 => 18393
    // 210 => 18394
    // 211 => 18424
    // 212 => 18426
    // 213 => 18586
    // 214 => 18587
    // 215 => 18588
    // 216 => 18590
    // 217 => 18591
    // 218 => 18592
    // 219 => 18593
    // 220 => 18594
    // 221 => 18595
    // 222 => 18596
    // 223 => 18597
    // 224 => 18598
    // 225 => 18599
    // 226 => 18600
    // 227 => 18601
    // 228 => 18602
    // 229 => 18604
    // 230 => 18605
    // 231 => 18607
    // 232 => 18608
    // 233 => 18609
    // 234 => 18610
    // 235 => 18613
    // 236 => 18615
    // 237 => 18616
    // 238 => 18618
    // 239 => 18621
    // 240 => 18623
    // 241 => 18624
    // 242 => 18626
    // 243 => 18627
    // 244 => 18628
    // 245 => 18629
    // 246 => 18631
    // 247 => 18633
    // 248 => 18635
    // 249 => 18637
    // 250 => 18639
    // 251 => 18640
    // 252 => 18641
    // 253 => 18642
    // 254 => 18644
    // 255 => 18646
    // 256 => 18647
    // 257 => 18648
    // 258 => 18649
    // 259 => 18650
    // 260 => 18654
    // 261 => 18655
    // 262 => 18656
    // 263 => 18657
    // 264 => 18661
    // 265 => 18666
    // 266 => 18670
    // 267 => 18671
    // 268 => 18672
    // 269 => 18673
    // 270 => 18674
    // 271 => 18675
    // 272 => 18676
    // 273 => 18677
    // 274 => 18679
    // 275 => 18681
    // 276 => 18683
    // 277 => 18684
    // 278 => 18685
    // 279 => 18687
    // 280 => 18708
    // 281 => 18709
    // 282 => 18710
    // 283 => 18723
    // 284 => 18724
    // 285 => 18725
    // 286 => 18728
    // 287 => 18731
    // 288 => 18733
    // 289 => 18734
    // 290 => 18736
    // 291 => 18738
    // 292 => 18739
    // 293 => 18741
    // 294 => 18742
    // 295 => 18743
    // 296 => 18744
    // 297 => 18745
    // 298 => 18746
    // 299 => 18747
    // 300 => 18748
    // 301 => 18749
    // 302 => 18750
    // 303 => 18753
    // 304 => 18754
    // 305 => 18768
    // 306 => 18782
    // 307 => 18783
    // 308 => 18784
    // 309 => 18785
    // 310 => 18787
    // 311 => 18788
    // 312 => 18791
    // 313 => 18792
    // 314 => 18793
    // 315 => 18797
    // 316 => 18798
    // 317 => 18799
    // 318 => 18800
    // 319 => 18802
    // 320 => 18805
    // 321 => 18810
    // 322 => 18811
    // 323 => 18812
    // 324 => 18813
    // 325 => 18814
    // 326 => 18818
    // 327 => 18819
    // 328 => 18820
    // 329 => 18821
    // 330 => 18822
    // 331 => 18823
    // 332 => 18824
    // 333 => 18829
    // 334 => 18925
    // 335 => 18926
    // 336 => 18927
    // 337 => 18928
    // 338 => 18929
    // 339 => 18930
    // 340 => 18931
    // 341 => 18932
    // 342 => 18934
    // 343 => 18939
    // 344 => 18954
    // 345 => 18955
    // 346 => 18956
    // 347 => 18957
    // 348 => 18958
    // 349 => 18960
    // 350 => 18962
    // 351 => 18964
    // 352 => 18965
    // 353 => 18966
    // 354 => 18970
    // 355 => 18973
    // 356 => 18974
    // 357 => 18975
    // 358 => 18976
    // 359 => 18978
    // 360 => 18979
    // 361 => 18980
    // 362 => 18981
    // 363 => 18982
    // 364 => 18983
    // 365 => 18984
    // 366 => 18993
    // 367 => 18996
    // 368 => 19010
    // 369 => 19013
    // 370 => 19014
    // 371 => 19015
    // 372 => 19016
    // 373 => 19020
    // 374 => 19023
    // 375 => 19036
    // 376 => 19037
    // 377 => 19039
    // 378 => 19040
    // 379 => 19041
    // 380 => 19042
    // 381 => 19043
    // 382 => 19045
    // 383 => 19046
    // 384 => 19049
    // 385 => 19051
    // 386 => 19052
    // 387 => 19054
    // 388 => 19060
    // 389 => 19061
    // 390 => 19062
    // 391 => 19063
    // 392 => 19064
    // 393 => 19065
    // 394 => 19066
    // 395 => 19067
    // 396 => 19068
    // 397 => 19069
    // 398 => 19070
    // 399 => 19073
    // 400 => 19074
    // 401 => 19088
    // 402 => 19089
    // 403 => 19090
    // 404 => 19092
    // 405 => 19095
    // 406 => 19096
    // 407 => 19099
    // 408 => 19100
    // 409 => 19110
    // 410 => 19112
    // 411 => 19129
    // 412 => 19130
    // 413 => 19136
    // 414 => 19138
    // 415 => 19145
    // 416 => 19153
    // 417 => 19156
    // 418 => 19165
    // 419 => 19179
    // 420 => 19180
    // 421 => 19181
    // 422 => 19187
    // 423 => 19196
    // 424 => 19201
    // 425 => 19204
    // 426 => 19205
    // 427 => 19206
    // 428 => 19209
    // 429 => 19210
    // 430 => 19211
    // 431 => 19212
    // 432 => 19213
    // 433 => 19215
    // 434 => 19216
    // 435 => 19217
    // 436 => 19218
    // 437 => 19220
    // 438 => 19221
    // 439 => 19223
    // 440 => 19224
    // 441 => 19225
    // 442 => 19229
    // 443 => 19230
    // 444 => 19231
    // 445 => 19233
    // 446 => 19234
    // 447 => 19235
    // 448 => 19236
    // 449 => 19247
    // 450 => 19248
    // 451 => 19264
    // 452 => 19274
    // 453 => 19276
    // 454 => 19279
    // 455 => 19290
    // 456 => 19292
    // 457 => 19294
    // 458 => 19295
    // 459 => 19296
    // 460 => 19297
    // 461 => 19298
    // 462 => 19299
    // 463 => 19303
    // 464 => 19304
    // 465 => 19305
    // 466 => 19306
    // 467 => 19316
    // 468 => 19317
    // 469 => 19324
    // 470 => 19369
    // 471 => 19378
    // 472 => 19380
    // 473 => 19381
    // 474 => 19386
    // 475 => 19387
    // 476 => 19388
    // 477 => 19389
    // 478 => 19393
    // 479 => 19395
    // 480 => 19397
    // 481 => 19399
    // 482 => 19401
    // 483 => 19402
    // 484 => 19407
    // 485 => 19409
    // 486 => 19423
    // 487 => 19424
    // 488 => 19428
    // 489 => 19467
    // 490 => 19468
    // 491 => 19469
    // 492 => 19470
    // 493 => 19471
    // 494 => 19473
    // 495 => 19475
    // 496 => 19476
    // 497 => 19477
    // 498 => 19478
    // 499 => 19481
    // 500 => 19484
    // 501 => 19486
    // 502 => 19516
    // 503 => 19526
    // 504 => 19530
    // 505 => 19533
    // 506 => 19549
    // 507 => 19550
    // 508 => 19561
    // 509 => 19566
    // 510 => 19567
    // 511 => 19568
    // 512 => 19571
    // 513 => 19578
    // 514 => 19580
    // 515 => 19584
    // 516 => 19589
    // 517 => 19590
    // 518 => 19592
    // 519 => 19596
    // 520 => 19599
    // 521 => 19600
    // 522 => 19601
    // 523 => 19602
    // 524 => 19603
    // 525 => 19606
    // 526 => 19612
    // 527 => 19614
    // 528 => 19616
    // 529 => 19617
    // 530 => 19620
    // 531 => 19621
    // 532 => 19622
    // 533 => 19623
    // 534 => 19625
    // 535 => 19626
    // 536 => 19627
    // 537 => 19628
    // 538 => 19629
    // 539 => 19633
    // 540 => 19636
    // 541 => 19637
    // 542 => 19638
    // 543 => 19657
    // 544 => 19659
    // 545 => 19660
    // 546 => 19662
    // 547 => 19664
    // 548 => 19665
    // 549 => 19667
    // 550 => 19670
    // 551 => 19673
    // 552 => 19682
    // 553 => 19684
    // 554 => 19703
    // 555 => 19719
    // 556 => 19767
    // 557 => 19768
    // 558 => 19781
    // 559 => 19808
    // 560 => 19809
    // 561 => 19967
    // 562 => 20032
    // 563 => 20122
    // 564 => 20168
    // 565 => 20175
    // 566 => 20183
    // 567 => 20184
    // 568 => 20195
    // 569 => 20212
    // 570 => 20215
    // 571 => 20216
    // 572 => 20229
    // 573 => 20242
    // 574 => 20243
    // 575 => 20244
    // 576 => 20246
    // 577 => 20247
    // 578 => 20248
    // 579 => 20249
    // 580 => 20250
    // 581 => 20254
    // 582 => 20264
    // 583 => 20267
    // 584 => 20278
    // 585 => 20290
    // 586 => 20291
    // 587 => 20310
    // 588 => 20312
    // 589 => 20316
    // 590 => 20327
    // 591 => 20331
    // 592 => 20332
    // 593 => 20333
    // 594 => 20334
    // 595 => 20335
    // 596 => 20337
    // 597 => 20341
    // 598 => 20342
    // 599 => 20343
    // 600 => 20344
    // 601 => 20345
    // 602 => 20346
    // 603 => 20347
    // 604 => 20349
    // 605 => 20351
    // 606 => 20352
    // 607 => 20358
    // 608 => 20359
    // 609 => 20365
    // 610 => 20368
    // 611 => 20371
    // 612 => 20372
    // 613 => 20377
    // 614 => 20405
    // 615 => 20416
    // 616 => 20428
    // 617 => 20432
    // 618 => 20438
    // 619 => 20441
    // 620 => 20444
    // 621 => 20446
    // 622 => 20447
    // 623 => 20448
    // 624 => 20456
    // 625 => 20457
    // 626 => 20459
    // 627 => 20461
    // 628 => 20463
    // 629 => 20464
    // 630 => 20466
    // 631 => 20467
    // 632 => 20468
    // 633 => 20472
    // 634 => 20474
    // 635 => 20476
    // 636 => 20491
    // 637 => 20492
    // 638 => 20495
    // 639 => 20499
    // 640 => 20507
    // 641 => 20515
    // 642 => 20531
    // 643 => 20532
    // 644 => 20537
    // 645 => 20538
    // 646 => 20539
    // 647 => 20543
    // 648 => 20547
    // 649 => 20548
    // 650 => 20554
    // 651 => 20555
    // 652 => 20557
    // 653 => 20560
    // 654 => 20561
    // 655 => 20566
    // 656 => 20569
    // 657 => 20571
    // 658 => 20580
    // 659 => 20581
    // 660 => 20584
    // 661 => 20585
    // 662 => 20593
    // 663 => 20603
    // 664 => 20604
    // 665 => 20606
    // 666 => 20607
    // 667 => 20608
    // 668 => 20610
    // 669 => 20614
    // 670 => 20615
    // 671 => 20620
    // 672 => 20628
    // 673 => 20637
    // 674 => 20638
    // 675 => 20639
    // 676 => 20643
    // 677 => 20657
    // 678 => 20659
    // 679 => 20661
    // 680 => 20664
    // 681 => 20665
    // 682 => 20668
    // 683 => 20669
    // 684 => 20670
    // 685 => 20671
    // 686 => 20696
    // 687 => 20703
    // 688 => 20713
    // 689 => 20715
    // 690 => 20719
    // 691 => 20723
    // 692 => 20726
    // 693 => 20729
    // 694 => 20731
    // 695 => 20734
    // 696 => 20735
    // 697 => 20739
    // 698 => 20741
    // 699 => 20744
    // 700 => 20753
    // 701 => 20755
    // 702 => 20757
    // 703 => 20767
    // 704 => 20771
    // 705 => 20772
    // 706 => 20778
    // 707 => 20779
    // 708 => 20783
    // 709 => 20785
    // 710 => 20796
    // 711 => 20797
    // 712 => 20800
    // 713 => 20806
    // 714 => 20807
    // 715 => 20814
    // 716 => 20815
    // 717 => 20821
    // 718 => 20846
    // 719 => 20849
    // 720 => 20854
    // 721 => 20864
    // 722 => 20875
    // 723 => 20876
    // 724 => 20888
    // 725 => 20890
    // 726 => 20906
    // 727 => 20912
    // 728 => 20914
    // 729 => 20915
    // 730 => 20933
    // 731 => 20938
    // 732 => 20959
    // 733 => 20961
    // 734 => 20969
    // 735 => 20974
    // 736 => 20981
    // 737 => 20982
    // 738 => 20984
    // 739 => 20988
    // 740 => 20992
    // 741 => 20998
    // 742 => 21012
    // 743 => 21020
    // 744 => 21021
    // 745 => 21022
    // 746 => 21028
    // 747 => 21032
    // 748 => 21034
    // 749 => 21039
    // 750 => 21063
    // 751 => 21074
    // 752 => 21082
    // 753 => 21088
    // 754 => 21093

}