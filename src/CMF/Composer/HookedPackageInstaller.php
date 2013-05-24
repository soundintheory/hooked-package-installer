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
        $type = $package->getType();

        $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        } else {
            $vendor = '';
            $name = $prettyName;
        }

        $availableVars = compact('name', 'vendor', 'type');

        $extra = $package->getExtra();
        if (!empty($extra['installer-name'])) {
            $availableVars['name'] = $extra['installer-name'];
        }

        if ($this->composer->getPackage()) {
            $pkg_extra = $this->composer->getPackage()->getExtra();
            if (!empty($pkg_extra['installer-paths'])) {
                $customPath = $this->mapCustomInstallPaths($pkg_extra['installer-paths'], $prettyName, $type);
                if ($customPath !== false) {
                    return $this->templatePath($customPath, $availableVars);
                }
            }
        }
        
        if (empty($extra['installer-path'])) {
            return parent::getInstallPath($package);
        }

        return $this->templatePath($extra['installer-path'], $availableVars);
    }
    
    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->callHook('pre-package-install', $package);
        parent::install($repo, $package);
        $this->callHook('post-package-install', $package);
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        $this->callHook('pre-package-update', $target);
        parent::update($repo, $initial, $target);
        $this->callHook('post-package-update', $target);
    }

    /**
     * {@inheritDoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->callHook('pre-package-uninstall', $package);
        parent::uninstall($repo, $package);
        $this->callHook('post-package-uninstall', $package);
    }
    
    /**
     * Attempts to call a hook
     * 
     * @param  string           $hookName
     * @param  PackageInterface $package
     */
    protected function callHook($hookName, PackageInterface $package)
    {
        // $composer, $package
        $extra = $package->getExtra();
        print("\n HOOK: $hookName\n");
        print_r($extra);
        
        if (empty($extra[$hookName])) return;
        
        $command = $extra[$hookName];
        $callable = is_callable($command);
        if (strpos($command, '::') !== false) {
            $parts = explode('::', $command);
            $class = $parts[0];
            $exists = class_exists($class);
            print("\n".$hookName.": ".$class." ".($exists ? "does exist" : "doesn't exist")."\n");
        }
        print("\n".$hookName.": ".$command." is ".($callable ? "callable" : "not callable")."\n");
    }

    /**
     * Replace vars in a path
     *
     * @param  string $path
     * @param  array  $vars
     * @return string
     */
    protected function templatePath($path, array $vars = array())
    {
        if (strpos($path, '{') !== false) {
            extract($vars);
            preg_match_all('@\{\$([A-Za-z0-9_]*)\}@i', $path, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $var) {
                    $path = str_replace('{$' . $var . '}', $$var, $path);
                }
            }
        }

        return $path;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
      return (bool)('hooked-package' === $packageType);
    }
}
