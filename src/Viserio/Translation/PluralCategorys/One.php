<?php
namespace Viserio\Translation\PluralCategorys;

use Viserio\Contracts\Translation\PluralCategory as CategoryContract;
use Viserio\Translation\Traits\NormalizeIntegerValueTrait;

class One implements CategoryContract
{
    use NormalizeIntegerValueTrait;

    /**
     * Returns category key by count.
     *
     * Locales: asa ast af bem bez bg bn brx ca cgg chr ckb da de dv ee el en eo es et eu fi fo fur fy gl gsw gu
     *          ha haw is it jmc kaj kcg kk kl ks ksb ku ky lb lg mas ml mn mr nah nb nd ne nl nn no nr ny nyn om
     *          or os pa pap ps pt rof rm rwk saq seh sn so sq ss ssy st sv sw syr ta te teo tig tk tn ts ur vo
     *          wae ve vun xh xog zu
     *
     * Languages:
     * - Asu (asa)
     * - ? (ast)
     * - Afrikaans (af)
     * - Bemba (bem)
     * - Bena (bez)
     * - Bulgarian (bg)
     * - Bengali (bn)
     * - Bodo (brx)
     * - Catalan (ca)
     * - Chiga (cgg)
     * - Cherokee (chr)
     * - ? (ckb)
     * - Danish (da)
     * - German (de)
     * - Divehi (dv)
     * - Ewe (ee)
     * - Greek (el)
     * - English (en)
     * - Esperanto (eo)
     * - Spanish (es)
     * - Estonian (et)
     * - Basque (eu)
     * - Finnish (fi)
     * - Faroese (fo)
     * - Friulian (fur)
     * - Western Frisian (fy)
     * - Galician (gl)
     * - Swiss German (gsw)
     * - Gujarati (gu)
     * - Hausa (ha)
     * - Hawaiian (haw)
     * - Icelandic (is)
     * - Italian (it)
     * - Machame (jmc)
     * - Jju (kaj)
     * - Tyap (kcg)
     * - Kazakh (kk)
     * - Kalaallisut (kl)
     * - Kirgistan (ks)
     * - Shambala (ksb)
     * - Kurdish (ku)
     * - ? (ky)
     * - Luxembourgish (lb)
     * - Ganda (lg)
     * - Masai (mas)
     * - Malayalam (ml)
     * - Mongolian (mn)
     * - Marathi (mr)
     * - Nahuatl (nah)
     * - Norwegian Bokmål (nb)
     * - North Ndebele (nd)
     * - Nepali (ne)
     * - Dutch (nl)
     * - Norwegian Nynorsk (nn)
     * - Norwegian (no)
     * - South Ndebele (nr)
     * - Nyanja (ny)
     * - Nyankole (nyn)
     * - Oromo (om)
     * - Oriya (or)
     * - ? (os)
     * - Punjabi (pa)
     * - Papiamento (pap)
     * - Pashto (ps)
     * - Portuguese (pt)
     * - Romansh (rm)
     * - Rombo (rof)
     * - Rwa (rwk)
     * - Samburu (saq)
     * - Sena (seh)
     * - Shona (sn)
     * - Somali (so)
     * - Albanian (sq)
     * - Swati (ss)
     * - Saho (ssy)
     * - Southern Sotho (st)
     * - Swedish (sv)
     * - Swahili (sw)
     * - Syriac (syr)
     * - Tamil (ta)
     * - Telugu (te)
     * - Teso (teo)
     * - Tigre (tig)
     * - Turkmen (tk)
     * - Tswana (tn)
     * - Tsonga (ts)
     * - Urdu (ur)
     * - ? (vo)
     * - Walser (wae)
     * - Venda (ve)
     * - Vunjo (vun)
     * - Xhosa (xh)
     * - Soga (xog)
     * - Zulu (zu)
     *
     * Rules:
     *  one   → n is 1;
     *  other → everything else
     *
     * @param int|string $count
     *
     * @return int
     */
    public function category($count): int
    {
        $count = $this->normalizeInteger($count);

        return $count === 1 ? 0 : 1;
    }
}
