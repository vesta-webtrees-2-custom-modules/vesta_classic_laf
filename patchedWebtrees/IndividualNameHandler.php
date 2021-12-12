<?php

namespace Cissee\WebtreesExt;

class IndividualNameHandler {
  
  protected $nickBeforeSurn = false;
  protected $appendXref = false;
          
  public function setNickBeforeSurn(bool $nickBeforeSurn) {
    $this->nickBeforeSurn = $nickBeforeSurn;
  }

  public function setAppendXref(bool $appendXref) {
    $this->appendXref = $appendXref;
  }
  
  public function addNick(string $nameForDisplay, string $nick): string {
    if ($this->nickBeforeSurn) {
      //same logic as in webtrees 1.x
      $pos = strpos($nameForDisplay, '/');
			if ($pos === false) {
				// No surname - just append it
				return $nameForDisplay . ' "' . $nick . '"';
			} else {
				// Insert before surname
				return substr($nameForDisplay, 0, $pos) . '"' . $nick . '" ' . substr($nameForDisplay, $pos);
			}
    } else {
      //same logic as in original webtrees 2.x, which has now changed to: 'don't display at all!'
      return $nameForDisplay;
    }
  }
  
  public function addXref(string $nameForDisplay, string $xref): string {
    if (!$this->appendXref or ('xref' == $xref)) {
      //'xref' indicates fake record, cf individual-name.phtml
      return $nameForDisplay;
    }
    return $nameForDisplay . ' (' . $xref . ')';
  }
  
  public function addBadges(string $gedcom): string {    
    $full = '';
    return $full;
    
    //experimental!
    
    //BIRT with SOUR (before next fact starts)
    if (preg_match('/\n1 BIRT(?!\n1)(.(?!\n1))*\n2 SOUR/sm', $gedcom, $match)) {
      //img source: "webtrees\resources\css\facts\BIRT.png"
      $full .= '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAMAAADW3miqAAADAFBMVEVHcEwCAgEAAAABAQAAAAAAAAAAAABqXygMDBjv3SQBAADaug4FBAIAAAELCw61nRzVthPAsTwUEQUECiuchxjTsw3KrRPSsw7QtB/VtQvUtRbGqxnWtxPHqREAAAAFBAAAAB8AAAAAAAOkhwBdSxazmhBORA48OB5KSDvQsAfIrBjSsQKejTKRhUKPgC2aiS1OSjt3ah6XiDrIqwynlC3DpxCrkQaqkxnMrxjSsxDHqQwcIjmsmC6/ow2plzK3nyDSsxDauAnUtQ/OtCfauhTQshHSsgTgvwvHqxPevAbdugDQsAW0nBzLrQ+MeiDEqBDLrx24nxvJrRwAADTHrByikjnSsQVoWhLEpwu/pRkpLD3LrAV4bz6umCR+cz+vlhPQsAiwlxHXtwvjwhLIqxPPsA+6oBMFBQLWuRO1mAIAAP2cizESGkLIqhHFri3RsAVlaXnApyaJgVSqmUG2nRa3nRPMrgnWuSF7cT+cjkZ9byPVtAU6MQJST0DLryCbjULBoworM1jYtwzgvxGhkDdjbJ5VTymkkjfUtA6/piC/qCjatwHKsCkdGw/cuw7UtRO2nh6rkxW1nRnLrRDWuBvdvAywmRnKrA7WthAhJkFXVD7QshPRtR2eiBVQRAOwmR2+qDO6oyZYVDxHPQPXuRuPfieYjEehkDS9pimznjPfvxW4mwbauhCnlTmXhzGsliVwaUubii/HryvAqznZuxi5nAe1oTLDpg9XTybKsB9YTheynSrOsRxjVg3WtxTAphplYlDFqRTGqx7EqybOrwnTswnNsBdpYj3JriPdvRd0ajDErS/MrgvkxBuvnDtyZyjGqQ3IryaVhCQ9MQCwmSHFrCzBpx7hwRfHrR2rlix3aBfWuBnZuxi0mxWhjCK8oQywmyKqlCXkwg2ylwu4nhjYtwqnkh+OgDbOrga4nxaIdhvVuB3aug/oxQ9YUz2zmx+ulhdmWyGKeBixlw/LrhjlwQHkwQjfvAbbuQfNsRrFqh3QsQ/NrxLStBLZuhjfvxVz83RuAAAA9XRSTlMAHxgjIQcqAQMBL/URDROt7AIKDzDn0OXs8eTX9eEdBRYaBBUJDCQUQpOPnkYmIYxVKUvqs7s6MeTQVzeKtsaPxN/ZsfPs3fjc+d/IhOGc2e7asBvev+9az+1J72rPdpjkqeP7+uHFHBgeBnImbOmJHnJCnm1k0/dBiWj9PEO4UJ0z3ec3DzBpbrgwr/QzwfGcn7nV/dCi2PcsN/Lal0TBzLBAOtx6hp7jq+98une4unyf38e2tLmhTc5UwpxFrc1J7+b1rdb1denLjNvZ1MJPrr22LMbG4tPZpF69w6ClxLW97sXQ7MOc6dJ61NH+Xr98SW/Bxj96YdsAAAIhSURBVDjLY2BAA4KCnAw4ADtQDiTJDuLgUMWOYArrquAySV2nMFcZSCsZLRTLz8ChKDvv5w9NIN22QMLxmyZ2NWrF5aa24qUM6jYBEVu+GRdxYFNkWOnSVOdS1rtos/T2fdyBtYzCWBTx1vd9FXUS8IvatnPP9X8bFdlYlLCoajHv/t7MPX/Deofb/68dUORiRLdQEIj1Ws29fnj62x1yepsSw8rHwocZRrpdIjoMZm6i4gdvvJbxi/p4ay87ctCB1bRLzPA27TBrsBCIDkkOfP4wMsXNCt0yo57fvpMtp0/it/CQdol+Fvvk8X1JMRG0yLGfMm0C/8S5/GKu+6/KyL7nj//gc3FFlZAQiqIS96kOlnOWeLhyy0o/4pZNTXpzJ8BOg1kOKRQ4GVS1q/tnh+wO3STunpD6PfGT/YPYlRUKbMysDMiqVKxslu46dVpKKvnFS0+vV+/iYmbysLDJKaGFk+GO4+dkjgpc+eKb9jUsPFidiZlZTggjxM+HnrANkrh08+nntK/elxlYhXmxxMvaw2cunDy2Li7C/574163B2NOK898jzlKRy6zjBe4mfgsz4MWqSEtyzdmfy2ctDk9IClplbMDMg02Rsvb3PyaNfDXzVjtK/jLJYWPGllQYMrX0rZnY2NREfDr1NdK5mOWxOyuLkYsrnUFVr0CBkY1LEVeO4WFmAuvnZWRmYsWZ9ZRAaYiTg4OVlZ1hFNAKAABNKaFSPuL5kQAAAABJRU5ErkJggg==" />';
    }

    //CHR with SOUR (before next fact starts)
    if (preg_match('/\n1 CHR(?!\n1)(.(?!\n1))*\n2 SOUR/sm', $gedcom, $match)) {
      //img source: "webtrees\resources\css\facts\CHR.png"
      $full .= '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAD30lEQVR4AWOgBRAMr2A0XvuMiSaGGyRWMmqUzmKSr13FJF65kqAlEgWzGFEEHv6njsMsSiYy4pLjbdnHiE8vX95sMY28fp+g8k4LmBhb4RJG6jgsIEZGs3SWu1Ldaj/58kX6+NRKpXSALWWxCFRUb1y1X6Nr+0nJjN7o9K4ZIhQ7xL2wXdewam69WtG0icrFM6eLV65YxlmzZSNz3c4exrL15vj0NkS6Sqg0rTvB27z7rVTNqtt2LUvL/8cwkB91fgX15toNK89r1K94pF6zZIFi5eJG0Yrl7bxNO/dyth74wtS49xpX/fZykdxpKuh6/zMwMJq1rJgr37jhtVzL1kOG7RvO6iTVZAKlBICYEYoJA6cJ28AKo7KKNDXqVpznrd7wi79m/TqZ7gOzJDv2rJCo37BUumbVdLGaNfO4G7bvE6jffJq3acc+7rYD3UKlSxxNPYLULAp7Q3UbV65RbFh7X7Bh6yaV1i3n3FoX95gxMMgDjVZmYmGVBdJcBB1jHRQLdsw1BgYmo7Lpc+Ratt0Va983W6XvwG7ZvsO3RToP3pJo3npYsm7dGsXG9fOkatfO5C9btpC7dvMp7rotN3lqN52QqFmzU7J61Q3JmtUPZBo3LJbsOrjDqG3dRefEfH+g0ZKMnDzaQFoRiDmJjirb0okhGnXL70h17puvP/3kDs2WdQ+NcrumayfV5ornTmvkr9tyULhl9xOxxi1XgY7bL1m2eIVo0dyNImVLDorVrtsr1Lp7t2jP0Z0yk04eVe/c8dyxenqHNguDMtBoFVY2NlUgLUycS3b9Z3Tw8JQwrZw1Q7lj51HpnkPLdFvW3bAvaC/VBWYeqEEKnGbeNmxJfXU8ZSv2KzauPavYsOaoZOXyrdKlC1aJVSzbIVy7/pxU686Hik0bHpgXdE90VJXUBOoTY2bjADlGDpT7iQ0cVp2K2eVKLZveCnccOKnZuf2aW930tlAGBlEGCJaHGsgLxDwixk5GAqk95VylK7byVa3bJd24abVy+7adKg1rTxhXzFzjFpcZrMTAIM0AwSqMjAygxC9IlEvU/v8Hpx/V6qXWgm1713E27rqv3rT2oUFCeQzYMcysqoyMjCrgXIIK2MT4eaQNbF2MDb3CXK0CY9xDwqNM/JX5QSEqBMSgBKwF1KsB8RSZ2V6iYkm5QvmC6coBqYGccup6oKgCWYAvdKG+lwRicRCG6lGBOooPnNXJBesXz+YKMFUXURDgUOIXFNIH5QxCcQ+1kAMapfxQzAPELAxUBNxALAMMcnGogwYeAB3DCo0SJoYhAUbBKBgFo2AUjIJRMApGwSgYBQDOiTUkxyOs/QAAAABJRU5ErkJggg==" />';
    }

    //DEAT with SOUR (before next fact starts)
    if (preg_match('/\n1 DEAT(?!\n1)(.(?!\n1))*\n2 SOUR/sm', $gedcom, $match)) {
      //img source: "webtrees\resources\css\facts\DEAT.png"
      $full .= '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAFdElEQVR4Ac2WW4hdVxnH/+tba6+197mcyVzSmTEmE2uoEVv7YFCskrEPihAvhSrog0IRWlGxDaZFfaigD4KCF5/EGmwV9UHEFhUfBGuxpemgJOroxCRMLiGdyVzOydnn7Mve6/IZyMTnZpIe83vebH78+a/v+3C7IXADfObhD4j59+0VB+/v8M+e+TMAMG5jRp/Qw5/92N5mq75/MFiJne++BOAf/zehRx87MhGZxacFZR/23g28L056774I4BhuIQqvEaXP3Bsn/oNF0d9UUXyRZPR2LhtfWL30pmUAayMXqu3gbYmwMgR93ii9SYImh311lzb9BxYXF/8A4OIIhVho9f45a3MBERLv7KyHHA9BrQmRUK9X7zh/7tUrAAa4SQiviQNkbVnryORJovZ5b3cNh3ncbMX9QRpOXJXpAYgAiNcloYc+/WQyt+9ke+GV00MAORC8Z74AyI2iyGB04omcKnK38OyzL28AaAIoADC2OPzEAQIQtv3K3nuwEyXJ3RNv3teaj6JwkAQS59OiqosTzuLfU1N75iKdH86LVE6M7ywG6XBvd5N+0evaCyap3tpsihTCnmSm87bKjwPoYxuIRw9/VADgV15ear7r3fd8rtnKvlrVPXKOT5mYZznAhxCuEMVDhp/x3rV01LkcQjZZVcFJ2SikhCEqdpKkhrVelEX1veHwwSMA/E3Noce/fOhbKqoeqevhQmBWwftUUsSCsNcY/xZmL60VK0JQXlunh8NqU6t4NYoaXaIgSLpZkuGu4ClxtvkEgGd++7s/CQC8rQ5l2caViSnt6sxtStEUxvAewCcA4qqiQke6krIiIkwIoYM3ZkNFfjdRMWNM47IxRkO4bDjwc1lW33f0Ry8+h2tc2ZZQVZfH0n5+TJKZM1qPhcDWWn+hqqs0G/C/Io1dcSwfNHGwRKQYaq2u2PlQv8H7cr/3esp7jxCa/yxy8TcAEwDEVQoA1Q0LPf/83/8yP38PT0503jNMubO+XqVAa6Xdnln76dPHXzr0EfOJ/fvvOKQiu5FlLjq3XP7m1FJ2fHqWdt8xHXY411PeiVrKxuqJ4xcvAUgAIZg53pbQ8inYhb9+7YWjT/1x4RtP/tp0dvhd0zPtMWOkA85OdjrvvNMktlHk1jvrRbut6fSZ/3RPn+mnQKsHDB0ADSw2ADSJSIYQcgB2e6X+yvz/Cvjtb74gAbQASADVxPjdY5/81NTX253i4xBypczhizz5yS9/vvRcmr7qVITNmdk4DFJnvJOyrhl1XXsAJYAcAN+KbU9b3/gjjz805/nS933oTmqtVoWQB7Kh+dWPf7j0VG1XSgCrAOzrvTrC9VmSNGRMhD3e2ygE3yURiEhMeqcJAG0liRHtMqCfrr4x0qyI0CchUgbVJBQpKa93UY1UiLlqSBIVUWPdB104C6uUHhA1SwA0ciHnuBJECQdvBMJ4YNGSSveLOs62/kMjPdCECNPMLvLBNRFCKzhJinwBaItr8EgTkpJbIYRSSn1JySgVAo45ADBiq/hhpAkxi6C1aleVmFKKDSCa1joDrBsAPHIh5+q+czxjre0CClKGHXVVdgSs5mtCGG2prV0tC99rNJrrYHWWKLqslGp32r6Ba4iRCimVFGXJqQ9lYM4LZuo558aTRjYGQIx8MHbGGhwncgzspiOD2MQUS4mdrXakt/pDIxUqS7EBrnpChN3gZkeSmpBSkpQNC8CPXOgH3/39cl4MvmNr9KuS7037LFwdv7i+ZtcAxACikb2yx7503/Wz5KiUdJpZveP82TycXFo71u2dCQAMgCFuAQLbpwVg11Yyfuv86OEmkbhBHvn8nfShByTOLRtZFsFfpQTQB5ACYNwGCNxC/gta/Lpwww0OLQAAAABJRU5ErkJggg==" />';
    }

    //BURI with SOUR (before next fact starts)
    if (preg_match('/\n1 BURI(?!\n1)(.(?!\n1))*\n2 SOUR/sm', $gedcom, $match)) {
      //img source: "webtrees\resources\css\facts\BURI.png"
      $full .= '<img width="16" src="data:image/png;base64,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" />';
    }

    if (preg_match('/\n1 _FSFTID/', $gedcom, $match)) {
      $full .= '<img width="16" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAMAAABg3Am1AAAAe1BMVEUAAAC6t7GFuUCFuUC6t7G6t7GblHqblHqFuUCblHqblHqblHqblHqblHqFuUCblHqblHqblHqFuUC6t7GblHq6t7G6t7GmoI26t7G6t7G2sqmuqpyNq1aFuUCFuUCHtUa6t7GFuUCblHq6t7G6t7GblHqFuUCrppaQp127S6TeAAAAJHRSTlMAgIBAQL9Av78wz4Bg7+/fjyAQz3BgIBDv36fPz8+fj3BwUDC6gAAMAAABTklEQVRIx7XT23KDIBCA4UVEA5JEc056brNp3/8Ju2wcbKoSmDH/td8AC8IUrWTbKhLIS5uMBosZtUgAM6Bmk4Od4LbRID9zKgEchVAMukOHgQIQDK7Fgr8XJ3z5EDgodXSq6+wTgUPrsk2HwT7nvqDAtoLAOqfeGYxFwBSUYaB48R6QmU8SKIEqQyC7+LKHgQ/pInB7hoOi1kNAgusKuin5QkAvEQ3fQySAJc730GscWMRqJ3xbyLhxQDtq8rNPwYUbHatGNHALXqV8GQc1Yt1NXjmQuf9v6Gm8/Txn0iB+3gFdJVpARP0PcINAI5YE4BZ0U+pnEfsgmPXglLvW9wDULfDdAxWBJgVs+Jnunr5P/mmEKwhUYPn9RaUJmBLRQmzIWYjO3dw84Xs+tYGEGuQ5QdqelimgQKpKXQKL1CXmOkHUycKkiqZybWCCfgFH7T9amDw6vQAAAABJRU5ErkJggg==" />';
    }
    
    return $full;
  }
}

