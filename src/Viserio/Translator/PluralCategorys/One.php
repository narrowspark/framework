<?php
namespace Viserio\Translator\PluralCategorys;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Viserio\Contracts\Translator\PluralCategory as CategoryContract;

/**
 * One.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class One implements CategoryContract
{
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
     * - ? (ks)
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
     * @param int $count
     *
     * @return string
     */
    public function category($count)
    {
        return $count === 1 ? 'one' : 'other';
    }
}
