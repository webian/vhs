<?php
namespace FluidTYPO3\Vhs\ViewHelpers\Menu;

/*
 * This file is part of the FluidTYPO3/Vhs project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

/**
 * ### Page: Browse Menu ViewHelper
 *
 * ViewHelper for rendering TYPO3 browse menus in Fluid
 *
 * Renders links to browse inside a menu branch including
 * first, previous, next, last and up to the parent page.
 * Supports both automatic, tag-based rendering (which
 * defaults to `ul > li` with options to set both the
 * parent and child tag names. When using manual rendering
 * a range of support CSS classes are available along
 * with each page record.
 */
class BrowseViewHelper extends AbstractMenuViewHelper
{

    /**
     * @var array
     */
    protected $backups = ['menu'];

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('labelFirst', 'string', 'Label for the "first" link', false, 'first');
        $this->registerArgument('labelLast', 'string', 'Label for the "last" link', false, 'last');
        $this->registerArgument('labelPrevious', 'string', 'Label for the "previous" link', false, 'previous');
        $this->registerArgument('labelNext', 'string', 'Label for the "next" link', false, 'next');
        $this->registerArgument('labelUp', 'string', 'Label for the "up" link', false, 'up');
        $this->registerArgument(
            'renderFirst',
            'boolean',
            'If set to FALSE the "first" link will not be rendered',
            false,
            true
        );
        $this->registerArgument(
            'renderLast',
            'boolean',
            'If set to FALSE the "last" link will not be rendered',
            false,
            true
        );
        $this->registerArgument(
            'renderUp',
            'boolean',
            'If set to FALSE the "up" link will not be rendered',
            false,
            true
        );
        $this->registerArgument(
            'usePageTitles',
            'boolean',
            'If set to TRUE, uses target page titles instead of "next", "previous" etc. labels',
            false,
            false
        );
        $this->registerArgument(
            'pageUid',
            'integer',
            'Optional parent page UID to use as top level of menu. If unspecified, current page UID is used'
        );
        $this->registerArgument(
            'currentPageUid',
            'integer',
            'Optional page UID to use as current page. If unspecified, current page UID from globals is used'
        );
        $this->registerArgument(
            'prevnextToSection',
            'boolean',
            'If set to TRUE, next/prev will link to the first page of the next section / to the last page of the previous section, too',
            false,
            false
        );
    }

    /**
     * @return string
     */
    public function render()
    {
        $defaultUid = $GLOBALS['TSFE']->id;
        $showAccessProtected = (boolean) $this->arguments['showAccessProtected'];
        $pageUid = (integer) (null !== $this->arguments['pageUid'] ? $this->arguments['pageUid'] : $defaultUid);
        $currentUid = (integer) ($this->arguments['currentPageUid'] ? $this->arguments['currentPageUid'] : $defaultUid);
        $currentPage = $this->pageService->getPage($currentUid, $showAccessProtected);
        $parentUid = (integer) (null !== $this->arguments['pageUid'] ? $pageUid : $currentPage['pid']);
        $parentPage = $this->pageService->getPage($parentUid, $showAccessProtected);
        $menuData = $this->getMenu($parentUid);
        if (empty($menuData)) {
            return !empty($this->arguments['as']) ? $this->renderChildren() : '';
        }
        $pageUids = array_keys($menuData);
        $uidCount = count($pageUids);
        $firstUid = $pageUids[0];
        $lastUid = $pageUids[$uidCount - 1];
        $nextUid = null;
        $prevUid = null;
        for ($i = 0; $i < $uidCount; $i++) {
            if ((integer) $pageUids[$i] === $currentUid) {
                if ($i > 0) {
                    $prevUid = $pageUids[$i - 1];
                }
                if ($i < $uidCount) {
                    $nextUid = $pageUids[$i + 1];
                }
                break;
            }
        }

        // !ian: Get prev/next of parent and childrens
        if (true === (boolean) $this->arguments['prevnextToSection']) {
            // get first children of next page (recursively)
//            if (null == $nextUid) {
//                $rootLineData = $this->pageService->getRootLine($pageUid);
//                $tmpParentUid = $rootLineData[count($rootLineData) - 1]['pid'];
//                $parentOfParentUid = $rootLineData[count($rootLineData) - 2]['pid'];
//                while ($nextUid !== null || (integer) $parentOfParentUid !== (integer) $rootLineData[0]['uid']) {
//                    $parentOfParentChildrensData = $this->getMenu($parentOfParentUid);
//                    $parentOfParentChildrensUids = array_keys($parentOfParentChildrensData);
//                    $parentOfParentChildrensUidCount = count($parentOfParentChildrensUids);
//                    for ($i = 0; $i < $parentOfParentChildrensUidCount; $i++) {
//                        if ((integer) $parentOfParentChildrensUids[$i] === (integer) $tmpParentUid) {
//                            if (isset($parentOfParentChildrensUids[$i + 1])) {
//                                $nextUid = $parentOfParentChildrensUids[$i + 1];
//                                $menuData[$nextUid] = $parentOfParentChildrensData[$nextUid];
//                                break 2;
//                            }
//                        }
//                    }
//
//                    $rootLineData = $this->pageService->getRootLine($tmpParentUid);
//                    $tmpParentUid = $rootLineData[count($rootLineData) - 1]['pid'];
//                    $parentOfParentUid = $rootLineData[count($rootLineData) - 2]['pid'];
//                }
//            }
//
//            // get last children of previous page (recursively)
//            if (null == $prevUid) {
//                $lastUidOfPrevLastChildren = null;
//                $lastMenuDataOfPrevLastChildren = [];
//                $uidOfPrevLastChildren = $prevUid;
//                $menuDataOfPrevLastChildren = $menuData;
//                while (null !== $uidOfPrevLastChildren) {
//                    $lastUidOfPrevLastChildren = $uidOfPrevLastChildren;
//                    $lastMenuDataOfPrevLastChildren = $menuDataOfPrevLastChildren;
//
//                    $menuDataOfPrevLastChildren = $this->getMenu($uidOfPrevLastChildren);
//                    $pageUidsOfPrevLastChildren = array_keys($menuDataOfPrevLastChildren);
//                    $uidCountOfPrevLastChildren = count($pageUidsOfPrevLastChildren);
//                    $uidOfPrevLastChildren = $pageUidsOfPrevLastChildren[$uidCountOfPrevLastChildren - 1];
//                }
//                $prevUid = $lastUidOfPrevLastChildren;
//                $menuData[$prevUid] = $lastMenuDataOfPrevLastChildren[$lastUidOfPrevLastChildren];
//            }
        }

        $pages = [];
        if (true === (boolean) $this->arguments['renderFirst']) {
            $pages['first'] = $menuData[$firstUid];
        }
        if (null !== $prevUid) {
            $pages['prev'] = $menuData[$prevUid];
        }
        if (true === (boolean) $this->arguments['renderUp']) {
            $pages['up'] = $parentPage;
        }
        if (null !== $nextUid) {
            $pages['next'] = $menuData[$nextUid];
        }
        if (true === (boolean) $this->arguments['renderLast']) {
            $pages['last'] = $menuData[$lastUid];
        }
        $menuItems = $this->parseMenu($pages);
        $menu = [];
        if (true === isset($pages['first'])) {
            $menu['first'] = $menuItems['first'];
            $menu['first']['linktext'] = $this->getCustomLabelOrPageTitle('labelFirst', $menuItems['first']);
        }
        if (true === isset($pages['prev'])) {
            $menu['prev'] = $menuItems['prev'];
            $menu['prev']['linktext'] = $this->getCustomLabelOrPageTitle('labelPrevious', $menuItems['prev']);
        }
        if (true === isset($pages['up'])) {
            $menu['up'] = $menuItems['up'];
            $menu['up']['linktext'] = $this->getCustomLabelOrPageTitle('labelUp', $menuItems['up']);
        }
        if (true === isset($pages['next'])) {
            $menu['next'] = $menuItems['next'];
            $menu['next']['linktext'] = $this->getCustomLabelOrPageTitle('labelNext', $menuItems['next']);
        }
        if (true === isset($pages['last'])) {
            $menu['last'] = $menuItems['last'];
            $menu['last']['linktext'] = $this->getCustomLabelOrPageTitle('labelLast', $menuItems['last']);
        }
        $this->backupVariables();
        $this->renderingContext->getVariableProvider()->add($this->arguments['as'], $menu);
        $output = $this->renderContent($menu);
        $this->renderingContext->getVariableProvider()->remove($this->arguments['as']);
        $this->restoreVariables();
        return $output;
    }

    /**
     * @param string $labelName
     * @param array $pageRecord
     * @return string
     */
    protected function getCustomLabelOrPageTitle($labelName, $pageRecord)
    {
        $title = $this->arguments[$labelName];
        if (true === (boolean) $this->arguments['usePageTitles']) {
            $title = $this->getItemTitle($pageRecord);
        }

        return $title;
    }
}
