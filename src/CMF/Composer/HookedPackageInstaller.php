<?php
/**
 * phpDocumentor
 *
 * PHP Version 5
 *
 * @copyright 2010-2013 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 */

namespace CMF\Composer;

use Composer\Repository\InstalledRepositoryInterface;
use Composer\Package\PackageInterface;

class HookedPackageInstaller extends \Composer\Installer\LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        return parent::getInstallPath($package);
    }
    
    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        print("\nCMF PRE INSTALL\n");
        parent::install($repo, $package);
        print("\nCMF POST INSTALL\n");
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        print("\nCMF PRE UPDATE\n");
        parent::update($repo, $initial, $target);
        print("\nCMF POST UPDATE\n");
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        print("\nCMF PRE UNINSTALL\n");
        parent::uninstall($repo, $package);
        print("\nCMF POST UNINSTALL\n");
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
      return (bool)('hooked-package' === $packageType);
    }
}
